<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CustomResponse;
use App\Models\Role;
use App\Models\Session;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use \Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    
    
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'username' => ['required'],
            'email' => ['required','email','max:255'],
            'password' => ['required','min:6'],
            'name' => ['required'],
            'role_id'=>['required','uuid'],
        ],[
            'required' => 'El Campo es requerido',
            'numeric'=>'El campo es un tipo decimal Ej: 10.00',
            'email'=>'El email es incorrecto',
            'min'=>'La contraseña debe tener al menos 6 caractéres',
        ]);

        if ($validator->fails()) {
            $r = CustomResponse::badRequest([
                'data'=>$validator->messages()
            ]);
            return response()->json($r, $r->code);
        }

        $existUserEmail = User::where('email',$request->email)->first();

        if($existUserEmail){
            $r = CustomResponse::badRequest("El correo ya existe");
            return response()->json($r);
        }

        $role = Role::where('id',$request->role_id)->first();

        if(!$role){
            $r = CustomResponse::badRequest("Error en las creadenciales");
            return response()->json($r);
        }

        try{
            $user = User::create([
                'name'=>$request->name,
                'username'=>$request->username,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'picture_url'=>'',
                'role_id'=>$role->id
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

            // $user = $user->get();
    
            $user->session = $session;
            
            $r = CustomResponse::ok([
                'user'=>$user,
                // 'token'=>$token
            ]);

            return response()->json($r);
            
        }catch(Exception $e){
            $r = CustomResponse::badRequest("Ocurrió un error en el servidor");
            return response()->json($r);
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
