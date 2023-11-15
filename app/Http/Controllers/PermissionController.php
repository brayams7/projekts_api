<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use Illuminate\Http\Request;
use App\Models\CustomResponse;
use App\Models\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Exception;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listPermission()
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
    *@param \app\Models\Permission
    *@return \Illuminate\Http\Response
     */
    public function deletePermission(Request $request,$PermissionId)
    {
        try {
            $this->authorize("update",Permission::class);

            $Permission= Permission::where('id',$PermissionId)->first();

            if(!$Permission){
                $r = CustomResponse::notFound("La Permiso no exite");
                return response()->json($r, $r->code);
            }
            $Permission->delete();
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
    public function updatePermission(Request $request,$PermissionId)
    {
        try{
        $Permission = Permission::where('id',$PermissionId)
                        ->first();
        if($Permission){
                        $Permission->name = $request->name;
                        $Permission->description = $request->description;
                        $Permission->save();
                        $r=CustomResponse::ok($Permission);
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
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createPermission(PermissionRequest $request)
    {
        try {
                $this->authorize("create",Permission::class);
                $Permission = Permission::create([
                'name'=>$request->name,
                'description'=>$request->description
            ]);
            $r = CustomResponse::ok($Permission);
            
                
                return response()->json($r, $r->code);

        }catch(AuthorizationException $e)
        {
            $r = CustomResponse::forbidden("No Autorizado");
            return response()->json($r,$r->code);
        } catch (Exception $e) {
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }
}