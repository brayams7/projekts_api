<?php

namespace App\Http\Controllers;

use App\Models\CustomResponse;
use App\Models\WorkspaceType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkspaceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list = WorkspaceType::all();
        $r = CustomResponse::ok([
            'data'=>$list,
        ]);

        return response()->json($r, $r->code);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => ['required','max:128'],
            'password' => ['required','min:6'],
            
        ],[
            'required' => 'El Campo es requerido',
            'min'=>'La contraseña debe tener al menos 6 caractéres',
        ]);

        if(!$validator->fails()){
            $r = CustomResponse::ok([
                'data'=>$validator->messages(),
            ]);
    
            return response()->json($r, $r->code);
        }

        try {

            $workspaceType = WorkspaceType::create([
                'name'=>$request->name,
            ]);

            $r = CustomResponse::ok([
                'data'=>$workspaceType,
            ]);
    
            return response()->json($r, $r->code);

        } catch (Exception $e) {
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
