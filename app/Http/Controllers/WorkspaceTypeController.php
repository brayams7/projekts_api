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

class WorkspaceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listWorkspaceTypes()
    {
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
            return response()->json($r.$r->code);
        }
        // $list = WorkspaceType::all();
        // $r = CustomResponse::ok([
        //   'data'=>$list,
        // ]);

        // return response()->json($r, $r->code);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createWorkspaceType(WorkspaceTypeRequest $request)
    {
        try {
                $this->authorize("create",WorkspaceType::class);
                $workspaceType = WorkspaceType::create([
                'name'=>$request->name,
            ]);

                $r = CustomResponse::ok($workspaceType);
                
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkspaceType  $workspaceType
     * @return \Illuminate\Http\Response
     */
    public function show(WorkspaceType $workspaceType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkspaceType  $workspaceType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WorkspaceType $workspaceType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkspaceType  $workspaceType
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkspaceType $workspaceType)
    {
        //
    }
}
