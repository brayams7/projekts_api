<?php

namespace App\Http\Controllers\Auth;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerificationEmailRequest;
use App\Jobs\SendEmailForEmailVerification;
use App\Mail\VerificationCode;
use App\Models\AttachmentType;
use App\Models\CustomResponse;
use App\Models\Role;
use App\Models\Session;
use App\Models\User;
use App\Utils\Util;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use \Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    private int $status = 1;
    
    public function register(RegisterRequest $request): JsonResponse{
        $name = $request->input('name','');
        $username = $request->input('username','');
        $email = $request->input('email','');
        $password = $request->input('password','');

        $role = Role::where('name',Constants::ROLE_TYPE_ADMIN)
            ->select('id')
            ->first();

        $isUserOrEmail = User::where(function ($query) use ($email, $username){
            $query->where('email', $email)
                ->orWhere('username', $username);
        })
            ->first();

        if(!$role){
            $r = CustomResponse::badRequest("Error en las creadenciales");
            return response()->json($r);
        }

        if($isUserOrEmail){
            $r = CustomResponse::badRequest("El usuario o correo ya están en uso");
            return response()->json($r);
        }

        if(!$request->hasFile('picture_url')){
            $r = CustomResponse::badRequest('Debes agregar una foto de perfil');
            return response()->json($r, $r->code);
        }

        $pictureFile = $request->file('picture_url');

        $fileName = Constants::NAME_DIRECTORY_PROFILE . time() . '.' . $pictureFile->getClientOriginalExtension();

        $attachmentType = AttachmentType::where('mimetype', $pictureFile->getClientMimeType())
                        ->first();

        if (!$attachmentType) {
            $r = CustomResponse::badRequest('El formato de la foto no es permitido');
            return response()->json($r, $r->code);
        }

        try{

            Storage::disk(Constants::NAME_STORAGE_CLOUD)->put($fileName,file_get_contents($pictureFile),'public');

            $urlPicture = env('URL_BASE_BUCKET') . $fileName;

            $verificationCode = Util::generateVerificationCode();

            $user = User::create([
                'name'=>$name,
                'username'=>$username,
                'email'=>$email,
                'password'=>Hash::make($password),
                'picture_url'=>$urlPicture,
                'role_id'=>$role->id,
                'verification_code'=>$verificationCode
            ]);

            $token = JWTAuth::fromUser($user); // Validar el token y obtener el usuario autenticado

            JWTAuth::setToken($token); //permite establecer manualmente un token JWT

            $payload = JWTAuth::getPayload();
            $expires = $payload->get('exp');

            $session = Session::create([
                'token'=>$token,
                'user_id'=>$user->id,
                'expires'=>$expires
            ]);

            $user->session = $session;

//            Mail::to($email)->send(new VerificationCode($verificationCode, $name));
            SendEmailForEmailVerification::dispatch($email, $verificationCode, $name);
            $r = CustomResponse::ok($user);
            return response()->json($r);
            
        }catch(Exception $e){
            $r = CustomResponse::badRequest("Ocurrió un error en el servidor");
            return response()->json($r);
        }
    }

    public function verifyEmail(VerificationEmailRequest $request) : JsonResponse{
        try {
            $email = $request->input('email');
            $verificationCode = $request->input('code');

            $user = User::where('email',$email)
                    ->where('verification_code',$verificationCode)
                    ->where('status', $this->status)
                    ->first();

            if(!$user){
                $r = CustomResponse::badRequest("El código de verificación es incorrecto");
                return response()->json($r,$r->code);
            }

            if($user->email_verified_at){
                $r = CustomResponse::ok("Esta cuenta ya ha sido verificado");
                return response()->json($r);
            }

            $user->email_verified_at = now()->timestamp;
            $user->save();

            $r = CustomResponse::ok();
            return response()->json($r);

        }catch (Exception $e){
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r,$r->code);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'email' => ['required','email','max:255'],
            'password' => ['required','min:6'],
            
        ],[
            'required' => 'El Campo es requerido',
            'email'=>'El email es incorrecto',
            'min'=>'La contraseña debe tener al menos 6 caractéres',
        ]);


        if ($validator->fails()) {
            $r = CustomResponse::badRequest([
                'data'=>$validator->messages()
            ]);
            return response()->json($r,$r->code);
        }

        $email = $request->email;
        $password = $request->password;

        $user = User::where('email', $email)
                ->where('status', 1)
                ->first();

        if(!$user || !Hash::check($password, $user->password)){
            $r = CustomResponse::badRequest("Error en las credenciales");
            return response()->json($r,$r->code);
        }

        // $user->session;
        $role = $user->role;
        $permissions = $role->permissions()->get();

        try {
            $token = JWTAuth::fromUser($user); //devuelve un token a partir del usuario.
            JWTAuth::setToken($token); //permite establecer manualmente un token JWT
            $payload = JWTAuth::getPayload();
            $expires = $payload->get('exp');

            //Actualizar o crear una sesion al usuario
            $session = Session::updateOrCreate( 
                [
                    'user_id'=>$user->id,
                    
                ],
                [
                    'token'=>$token,
                    'expires'=>$expires
                ],
                
            );

            $user->session = $session;
            
            $r = CustomResponse::ok([
                'user'=>$user,
                'permissions'=>$permissions,
                'role'=>$role
            ]);
    
            return response()->json($r);
        } catch (JWTException $e) {
            $r = CustomResponse::badRequest("Error en las credenciales");
            return response()->json($r,$r->code);
        }
        
    }


    public function refreshToken(Request $request): JsonResponse
    {
        $authorizationHeader = $request->header('Authorization');
            
        if (!$authorizationHeader) {
            $r = CustomResponse::unAuthorized("NO autorizado");
            return response()->json($r);
        }

        $token = str_replace('Bearer ', '', $authorizationHeader);
        
        try {
            
            $payload = JWTAuth::parseToken()->getPayload();
            $expires = $payload->get('exp');
            $userId = $payload->get('sub');

            $user = User::where('id',$userId)
                    ->where('status',1)
                    ->first();

            if(!$user){
                $r = CustomResponse::unAuthorized("NO autorizado 2");
                return response()->json($r);
            }
            
            $newToken = JWTAuth::fromUser($user);

            Session::updateOrCreate( 
                [
                    'user_id'=>$user->id,
                    
                ],
                [
                    'token'=>$newToken,
                    'expires'=>$expires
                ],
                
            );
            $r = CustomResponse::ok([
                'newToken'=>$newToken,
            ]);
            return response()->json($r);
            
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                $r = CustomResponse::unAuthorized("Token invalido");
                return response()->json($r, $r->code);

            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                try {

                    // Intentamos obtener un nuevo token de acceso usando el "refresh token"
                    $newToken = JWTAuth::refresh();

                    JWTAuth::setToken($newToken); //permite establecer manualmente un token JWT

                    $payload = JWTAuth::getPayload();
                    $expires = $payload->get('exp');
                    $userId = $payload->get('sub');

                    $user = User::where('id',$userId)
                    ->where('status',1)
                    ->first();

                    Session::updateOrCreate(
                        [
                            'user_id'=>$user->id,

                        ],
                        [
                            'token'=>$newToken,
                            'expires'=>$expires
                        ],

                    );

                    // Devolvemos la respuesta con el nuevo token y el "refresh token"
                    $r = CustomResponse::ok([
                        'token'=>$newToken,
                        'expires'=>$expires
                    ]);

                    return response()->json($r,$r->code);

                } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                    // No se pudo generar un nuevo token (por ejemplo, el "refresh token" ha expirado o es inválido)
                    $r = CustomResponse::unAuthorized("NO autorizado");
                    return response()->json($r,$r->code);
                }

            }else{
                $r = CustomResponse::unAuthorized("No autorizado");
                return response()->json($r,$r->code);
            }
        }

    }

    
}
