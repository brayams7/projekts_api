<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use App\Models\CustomResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Validation\ValidationException;
use \Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private int $status = 1;

    public function searchUsersByEmailOrUsername(Request $request):JsonResponse{
        try {
            $text = $request->query('text');

            if(!$text){
                $r = CustomResponse::ok([]);
                return response()->json($r);
            }

            $users = User::where(function($query) use($text){
                    $query->where('username', 'LIKE', '%' . $text . '%')
                    ->orWhere('email', 'LIKE', '%' . $text . '%');
                })
                ->where("status", $this->status)
                ->get();

            $r = CustomResponse::ok($users);
            return response()->json($r);
        }catch (\Exception $e){
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }
    
    public function toggleUserStatus(Request $request, $id): JsonResponse
    {
        try {
            $user = User::where('id',$id)->first();
            $status=$request->query('status');
            if (!$status && !isset($status)){
                $r = CustomResponse::notFound("Es necesita enviar un status en la URL");
                return response()->json($r, $r->code);
            }
            if($user){
                $instantaneous=intval($status);

                if($instantaneous === 1 || $instantaneous === 0) {

                    $user->update(['status' => $instantaneous]);
                    $statusMessage = $instantaneous == 1 ? 'habilitado' : 'deshabilitado';
                    $r = CustomResponse::ok($statusMessage);
                    return response()->json($r);

                }else{
                    $r = CustomResponse::notFound("el valor no es conocido");
                    return response()->json($r, $r->code);
                }
            }else {
                $r = CustomResponse::notFound("el Usuario no exite");
                return response()->json($r, $r->code);
            }
        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function isUniqueUsername(Request $request):JsonResponse{
        try {
            $validator = Validator::make($request->all(),
                [
                    'username'=>'required'
                ],
                [
                    'username'=>'El campo es requerido'
                ]
            );

            if($validator->fails()){
                $r = CustomResponse::badRequest($validator->messages());
                return response()->json($r, $r->code);
            }

            $username = $request->input('username');

            $user = User::where('username', $username)->first();

            $r = CustomResponse::ok((bool) $user);

            return response()->json($r,$r->code);

        }catch (\Exception $e){
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r);
        }
    }

    public function isUniqueEmail(Request $request):JsonResponse{
        try {
            $validator = Validator::make($request->all(),
                [
                    'email'=>'required|email'
                ],
                [
                    'email'=>'El campo dede ser un correo'
                ]
            );

            if($validator->fails()){
                $r = CustomResponse::badRequest($validator->messages());
                return response()->json($r, $r->code);
            }

            $email = $request->input('email');

            $user = User::where('email', $email)->first();

            $r = CustomResponse::ok((bool) $user);

            return response()->json($r,$r->code);

        }catch (\Exception $e){
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r);
        }
    }
    public function updateProfile(UserRequest $request, $id): JsonResponse
    {
        try {
            $user = User::where('id',$id)
                ->where('status',$this->status)
                ->first();

            if(!$user){
                $r = CustomResponse::notFound("Usuario no encontrado");
                return response()->json($r);
            }

            $user ->username = $request->username;
            $user ->name = $request->name;
            $user ->email = $request->email;
            $user -> color = $request->color;

            if ($request->hasFile('picture_url')) {

                $picture_url = $user->picture_url;

                if(is_string($picture_url) && $picture_url && strlen($picture_url) > 0){

                    $storage = Storage::disk(Constants::NAME_STORAGE_CLOUD);
                    $name = substr($user->picture_url,strlen(env('URL_BASE_BUCKET'))-1,strlen($user->picture_url)-1);
                    if($storage->exists($name)){
                        $storage->delete($name);
                    }
                }

                $file = $request->file('picture_url');

                $extension = strtolower($file->getClientOriginalExtension());

                $filename = Constants::NAME_DIRECTORY_PROFILE . time() . '.' . $extension;

                $url = env('URL_BASE_BUCKET').$filename;

                Storage::disk(Constants::NAME_STORAGE_CLOUD)->put($filename,file_get_contents($file),'public');
                $user-> picture_url = $url;

            }else{

                $filename = substr($user->picture_url,strlen(env('URL_BASE_BUCKET'))-1,strlen($user->picture_url)-1);
                Storage::disk(Constants::NAME_STORAGE_CLOUD)->delete($filename);
                $user->picture_url = '';
            }

            $user -> save();
            $r = CustomResponse::ok($user);
            return response()->json($r);

        } catch (ModelNotFoundException $e) {

            $r = CustomResponse::notFound("Usuario no encontrado");
            return response()->json($r);

        } catch (ValidationException $e) {

            $errors = $e->validator->errors()->all();
            $r = CustomResponse::unprocessableEntity("Error de validación: " . implode(', ', $errors));
            return response()->json($r);

        } catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r);
        }
    }
}

