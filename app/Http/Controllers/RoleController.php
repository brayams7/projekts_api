<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\PermissionRequest;
use App\Models\CustomResponse;
use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listRole()
    {
        try{
            $this->authorize("viewAny",Role::class);

            $list = Role::all();
            $r = CustomResponse::ok($list);
            
            
            return response()->json($r, $r->code);
        }catch(AuthorizationException $e)
        {
            $r = CustomResponse::forbidden("No Autorizado");
            return response()->json($r,$r->code);
        }catch(Exception $e){
            $r = CustomResponse::badRequest("Ocurrio un error en el servidor");
            return response()->json($r.$r->code);
        }}
        /**
     * Display the specified resource.
     *
    *@param \app\Models\Permission
    *@return \Illuminate\Http\Response
     */
    public function deleteRole(Request $request,$RoleId)
    {
        try {
            $this->authorize("update",Role::class);

            $Role= Role::where('id',$RoleId)->first();

            if(!$Role){
                $r = CustomResponse::notFound("La Role no exite");
                return response()->json($r, $r->code);
            }
            $Role->delete();
            $r = CustomResponse::ok("OK");
            return response()->json($r);
        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);
        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }
    /**
     * Display the specified resource.
     *
    *@param \app\Models\Permission
    *@return \Illuminate\Http\Response
     */
    public function updateRole(Request $request,$RoleId)
    {
        try{
        $Role = Role::where('id',$RoleId)
                        ->first();
        if($Role){
            $Role->name = $request->name;
            $Role->description = $request->description;
            $Role->save();
            $r=CustomResponse::ok($Role);
            return response()->json($r);
        }else{
            $r = CustomResponse::notFound("El Permiso no fue encontrado");
            return response()->json($r);
        }
        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);  
        }catch (Exception $e) {
            $r = CustomResponse::badRequest("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }
}
