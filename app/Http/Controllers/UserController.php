<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use App\Models\CustomResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
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
    public function updateProfile(UserRequest $request, $id): JsonResponse
    {
        try {
            $user = User::where('id',$id)->first();

            $username = $request->has('username') ? $request->username : $user->username;
            $user ->name = $request->has('name') ? $request->name : $user->name;
            $user ->email = $request->has('email') ? $request->email : $user->email;

            if ($request->hasFile('picture_url')) {

                $allowedExtensions = ['jpeg', 'png', 'jpg', 'svg'];
                $file = $request->file('picture_url');

                $extension = strtolower($file->getClientOriginalExtension());

                if ($file->isValid() && in_array($extension, $allowedExtensions)) {

                    $filename = Constants::NAME_DIRECTORY_PROFILE . time() . '.' . $extension;
                    $url = env('URL_BASE_BUCKET').$filename;
                    Storage::disk(Constants::NAME_STORAGE_CLOUD)->put($filename,file_get_contents($file),'public');
                    $user-> picture_url = $url;

                } else {

                    $allowedExtensionsString = implode(', ', $allowedExtensions);
                    $r = CustomResponse::unprocessableEntity($allowedExtensionsString);
                    return response()->json($r);

                }
            }

            $user->username = $username;
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

