<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use Illuminate\Http\Request;
use App\Models\CustomResponse;
use App\Models\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;
use \Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function listPermission(): JsonResponse
    {
        try{
            $this->authorize("viewAny",Permission::class);

            $list = Permission::all();
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
     * @param $PermissionId
     * @return JsonResponse
     */
    public function deletePermission(Request $request, $PermissionId): JsonResponse
    {
        try {
            $this->authorize("update",Permission::class);

            $permission= Permission::where('id',$PermissionId)->first();

            if(!$permission){
                $r = CustomResponse::notFound("La Permiso no exite");
                return response()->json($r, $r->code);
            }

            $permission->delete();

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
     * @param $PermissionId
     * @return JsonResponse
     */
    public function updatePermission(Request $request,$PermissionId): JsonResponse
    {
        try{
        $Permission = Permission::where('id',$PermissionId)
                        ->first();
        if(!$Permission){
            $r = CustomResponse::notFound("El Permiso no fue encontrado");
            return response()->json($r);

        }

        $Permission->name = $request->name;
        $Permission->description = $request->description;
        $Permission->save();
        $r=CustomResponse::ok($Permission);
        return response()->json($r);

        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);
        }catch (Exception $e) {
            $r = CustomResponse::badRequest("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PermissionRequest $request
     * @return JsonResponse
     */
    public function createPermission(PermissionRequest $request): JsonResponse
    {
        try {
            $this->authorize("create",Permission::class);

            $Permission = Permission::create([
                'name'=>$request->name,
                'description'=>$request->description
            ]);
            $r = CustomResponse::ok($Permission);
            return response()->json($r, $r->code);

        }catch(AuthorizationException $e) {

            $r = CustomResponse::forbidden("No Autorizado");
            return response()->json($r,$r->code);

        } catch (Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }
}