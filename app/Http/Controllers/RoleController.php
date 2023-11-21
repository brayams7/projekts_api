<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Exception;
use Illuminate\Http\Request;
use App\Models\CustomResponse;
use App\Models\Role;
use \Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function listRole(): JsonResponse
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
     * @param Request $request
     * @param $RoleId
     * @return JsonResponse
     */
    public function deleteRole(Request $request,$RoleId): JsonResponse
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
     * @param Request $request
     * @param $RoleId
     * @return JsonResponse
     */
    public function updateRole(Request $request,$RoleId): JsonResponse
    {
        try{

        $role = Role::where('id',$RoleId)
                        ->first();
        if(!$role){
            $r = CustomResponse::notFound("El Permiso no fue encontrado");
            return response()->json($r);
        }
        $role->name = $request->name;
        $role->description = $request->description;
        $role->save();
        $r=CustomResponse::ok($role);
        return response()->json($r);

        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);  
        }catch (Exception $e) {
            $r = CustomResponse::badRequest("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }
}
