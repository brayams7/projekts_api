<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardStageRequest;
use App\Http\Requests\StoreStageRequest;
use App\Models\Board;
use App\Models\CustomResponse;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Illuminate\Http\JsonResponse;
class StageController extends Controller
{
    protected int $status = 1;

    /*public function getListStagesByBoard(Request $request, int $boardId){

        try {
            $board = Board::where('id', $boardId)
                ->where('status', $this->status)
                ->with(['stages' => function ($query) {
                    $query->orderBy('board_stage.order');
                }])
                ->first();
        }catch (\Exception $e){
            $r = CustomResponse::badRequest("Error en los datos proporcionados");
            return response()->json($r, $r->code);
        }

            foreach ($board->stages as $stage){
              $stage->pivot;
            }

        $r = CustomResponse::ok($board);
        return response()->json($r);
    }*/


    public function createStageAndAssignToBoard(StoreStageRequest $request, $boardId): JsonResponse
    {

          try {
              $description = $request->description ? $request->description : '';
              $color = $request->color ? $request->color : '';

              $stage = Stage::create([
                  'name'=>$request->name,
                  'description'=>$description,
                  'color'=>$color,
                  'is_default'=>$request->is_default,
                  'is_final'=>$request->is_final
              ]);

              $board = Board::where('id', $boardId)
                  ->where('status', $this->status)
                  ->first();

              if(!$board){
                  $r = CustomResponse::badRequest("Error en los datos proporcionados");
                  return response()->json($r, $r->code);
              }

              $lastOrder = $board->stages()->max('order');

              $board->stages()->attach($stage->id,['order'=>$lastOrder+1]);

              $r = CustomResponse::ok($stage);
              return response()->json($r);
          }catch (\Exception $e){

              $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
              return response()->json($r, $r->code);
          }
    }

    public function changeOrderStagesByBoard(StoreBoardStageRequest $request, $boardId): JsonResponse
    {
        $board = Board::where('id', $boardId)
            ->where('status', $this->status)
            ->first();

        if(!$board){
            $r = CustomResponse::badRequest("Error en los datos proporcionados");
            return response()->json($r, $r->code);
        }

        $arrayModifiedOrder = $request->modified_order;
        $usedOrders = [];
        //asort($arrayModifiedOrder);

        try {
            //$currentStages = $board->stages->sortBy('pivot.order');
            DB::beginTransaction();

            foreach ($board->stages as $stage) {
                if(isset($arrayModifiedOrder[$stage->id])){
                    $newPosition = $arrayModifiedOrder[$stage->id];

                    if (in_array($newPosition, $usedOrders)) {
                        DB::rollBack();
                        $r = CustomResponse::badRequest("Los números de orden deben ser únicos");
                        return response()->json($r, $r->code);
                    }

                    $pivotAttributes = ['order' => $newPosition];

                    $board->stages()->updateExistingPivot($stage->id, $pivotAttributes);
                    $usedOrders[] = $newPosition;

                }
            }

            DB::commit();
            $r = CustomResponse::ok("Ok");
            return response()->json($r);
        }catch (\Exception $e){
            DB::rollBack();
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }

    public function updateStage(StoreStageRequest $request, $stageId): JsonResponse
    {
        try {

            $stage = Stage::where("id",$stageId)
                    ->first();

            if(!$stage){
                $r = CustomResponse::badRequest("Error en los datos proporcionados");
                return response()->json($r, $r->code);
            }

            $description = $request->description ? $request->description : $stage->description;
            $color = $request->color ? $request->color : $stage->color;

            $stage->name = $request->name;
            $stage->description = $description;
            $stage->color = $color;
            $stage->is_default = $request->is_default;
            $stage->is_final = $request->is_final;

            $stage->save();

            $r = CustomResponse::ok("OK");
            return response()->json($r);
        }catch (\Exception $e){

            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }

    public function updateOrderStagesByBoard(StoreBoardStageRequest $request, $boardId): JsonResponse
    {
        $board = Board::where('id', $boardId)
            ->where('status', $this->status)
            ->first();

        if(!$board){
            $r = CustomResponse::badRequest("Error en los datos proporcionados");
            return response()->json($r, $r->code);
        }

        if(!isset($request->modified_order["stageId"]) || !isset($request->modified_order["newOrder"]) ){
            $r = CustomResponse::badRequest("Error en las credenciales");
            return response()->json($r, $r->code);
        }

        $stageId = $request->modified_order["stageId"];
        $stage = $board->stages->where('id',$stageId)
            ->first();

        if(!$stage){
            $r = CustomResponse::badRequest("Error en las credenciales");
            return response()->json($r, $r->code);
        }

        $currentOrderStage = $stage->pivot->order;
        $newOrderStage = $request->modified_order["newOrder"];

        try {
            DB::beginTransaction();

            if($newOrderStage > $currentOrderStage){

                $stages = $board->stages()
                    ->wherePivot('order', '>=', $currentOrderStage)
                    ->wherePivot('order', '<=', $newOrderStage)
                    ->withPivot('order')
                    ->get();

                foreach ($stages as $currentStage) {


                    if ($currentStage->id === $stage->id) {

                        //echo $currentStage->pivot->order;

                        $pivotAttributes = ['order' => $newOrderStage];

                    } else {

                        $pivotAttributes = ['order' => $currentStage->pivot->order - 1];
                    }

                    //echo $currentStage->pivot->order;

                    $board->stages()->updateExistingPivot($currentStage->id, $pivotAttributes);
                }
            }else{
                $stages = $board->stages()
                    ->wherePivot('order', '>=', $newOrderStage)
                    ->wherePivot('order', '<=', $currentOrderStage)
                    ->withPivot('order')
                    ->get();

                foreach ($stages as $currentStage) {
                    //echo $currentStage->pivot->order;

                    if ($currentStage->id === $stage->id) {

                        //echo $currentStage->pivot->order;

                        $pivotAttributes = ['order' => $newOrderStage];

                    } else {

                        $pivotAttributes = ['order' => $currentStage->pivot->order + 1];
                    }

                    $board->stages()->updateExistingPivot($currentStage->id, $pivotAttributes);
                }
            }

            DB::commit();
            $r = CustomResponse::ok("OK");
            return response()->json($r);
        }catch (\Exception $e){
            DB::rollBack();
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }


}
