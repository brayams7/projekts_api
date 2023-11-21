<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use App\Models\CustomResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\UserRequest;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private int $status = 1;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = DB::table('users')->get();

        return response()->json([
            'data'=>$users
        ],200);
    }

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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function toggleUserStatus(Request $request, $id)
    {
        try {
            $user = User::where('id',$id)->first();
            $status=$request->query('status');
            if (!$status && !isset($status)){
                $r = CustomResponse::notFound("Es necesita enviar un status en la URL");
                return response()->json($r, $r->code);
            }
            if($user){
                $statustoint=intval($status);
                if($statustoint === 1 || $statustoint === 0)
                {
                    $user->update(['status' => $statustoint]);
                    $statusMessage = $statustoint == 1 ? 'habilitado' : 'deshabilitado';
                    $r = CustomResponse::ok($statusMessage);
                    return response()->json($r);
                }else{
                    $r = CustomResponse::notFound("el valor no es conocido");
                    return response()->json($r, $r->code);
                }
            }else
            {
                $r = CustomResponse::notFound("el Usuario no exite");
                return response()->json($r, $r->code);
            }
        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }
    public function updateProfile(UserRequest $request, $id)
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $r = CustomResponse::notFound("Usuario no encontrado");
            return response()->json($r);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $r = CustomResponse::unprocessableEntity("Error de validación: " . implode(', ', $errors));
            return response()->json($r);
        } catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r);
        }
    }
}

