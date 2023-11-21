<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkspaceTypeRequest;
use App\Models\CustomResponse;
use App\Models\Workspace;
use App\Models\WorkspaceType;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Http\JsonResponse;
class WorkspaceTypeController extends Controller{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse{
        try {

            $list = WorkspaceType::all();

            $r = CustomResponse::ok([
                'data'=>$list,
            ]);

            return response()->json($r);

        }catch (Exception $e){
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function listWorkspaceTypes(): JsonResponse{
        try{
            $this->authorize("viewAny",WorkspaceType::class);

            $list = WorkspaceType::all();
            $r = CustomResponse::ok($list);
            
            
            return response()->json($r, $r->code);
        }catch(AuthorizationException $e)
        {
            $r = CustomResponse::forbidden("No Autorizado");
            return response()->json($r,$r->code);
        }catch(Exception $e){
            $r = CustomResponse::badRequest("Ocurrio un error en el servidor");
            return response()->json($r->code);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param WorkspaceTypeRequest $request
     * @return JsonResponse
     */
    public function createWorkspaceType(WorkspaceTypeRequest $request): JsonResponse
    {
        try {
            $this->authorize("create",WorkspaceType::class);

            $workspaceType = WorkspaceType::create([
                'name'=>$request->name,
            ]);

            $r = CustomResponse::ok($workspaceType);
                
            return response()->json($r, $r->code);

        }catch(AuthorizationException $e) {
            $r = CustomResponse::forbidden("No Autorizado");
            return response()->json($r,$r->code);
        } catch (Exception $e) {
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $workspaceTypeId
     * @return JsonResponse
     * app
     */
    public function updateWorkspaceType(Request $request,$workspaceTypeId): JsonResponse
    {
        try{
        $workspaceType = WorkspaceType::where('id',$workspaceTypeId)
                        ->first();
        if($workspaceType){
            $workspaceType->name = $request->name;
            $workspaceType->save();
            $r=CustomResponse::ok($workspaceType);
        }else{
            $r = CustomResponse::notFound("El Espacio de trabajo no fue encontrado");
        }
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
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param $workspaceTypeId
     * @return JsonResponse
     */
    public function deleteWorkspaceType(Request $request,$workspaceTypeId): JsonResponse
    {
        try {
            $this->authorize("update",WorkspaceType::class);

            $workspaceType= WorkspaceType::where('id',$workspaceTypeId)->first();

            if(!$workspaceType){
                $r = CustomResponse::notFound("La Espacio de trabajo no exite");
                return response()->json($r, $r->code);
            }
            $workspaceType->delete();
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
}
