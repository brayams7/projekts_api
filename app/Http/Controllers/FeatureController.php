<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChangeOrderFeatureRequest;
use App\Http\Requests\StoreFeatureRequest;
use App\Models\Board;
use App\Models\CustomResponse;
use App\Models\Feature;
use App\Models\Stage;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class FeatureController extends Controller
{
    private int $status = 1;

    public function getFeaturesByStage(Request $request, $stageId):JsonResponse{

        $stage = Stage::where("id",$stageId)
            ->first();

        if(!$stage){
            $r = CustomResponse::badRequest("Error en los datos proporcionados");
            return response()->json($r, $r->code);
        }

        $boardId = 48;

        $orderedFeatures = Feature::with([
            'stages' => function ($query) use ($boardId, $stageId) {
                $query->where('board_id', $boardId)
                    ->where('stage_id', $stageId)
                    ->orderBy('order');
            }
        ])->get();

        $features = Feature::whereHas('stages', function ($query) use ($stageId, $boardId) {
            $query->where('stage_id', $stageId)
                ->where('board_id', $boardId);
        })
            ->orderBy('order')
            ->get();

        $r = CustomResponse::ok($features);
        return response()->json($r);
    }

    /**
     * Ordena las features de de un estado.
     */
    private function orderVerticalFeaturesToStage(int $minOrder, int $maxOrder, $feature, $stage, $boardId, $newOrderFeature, $currentOrderFeature): bool
    {
        $isOk = true;
        try {

            $features = $stage->features()
                ->wherePivot('board_id',$boardId)
                ->wherePivot('order', '>=', $minOrder)
                ->wherePivot('order', '<=', $maxOrder)
                ->withPivot('order')
                ->get();

            foreach ($features as $featureStage){

                if($featureStage->id === $feature->id){

                    $pivotAttributes = ['order'=>$newOrderFeature];

                }else{

                    if($newOrderFeature > $currentOrderFeature){

                        $pivotAttributes = ['order'=>$featureStage->order - 1];

                    }else{

                        $pivotAttributes = ['order'=>$featureStage->order + 1];

                    }
                }

                $featureStage->stages()->updateExistingPivot($stage->id, $pivotAttributes);
                $featureStage->order = $pivotAttributes['order'];
                $featureStage->save();
            }

        }catch (\Exception $e){
            echo "entro";
            $isOk = false;
        }

        return $isOk;
    }

    private function orderFeaturesWhenMovingToAnotherStage($stage, $boardId, $order, $feature, $isOrderAsc): bool
    {
        $isOk = true;

        try {
            $features = $stage->features()
                ->wherePivot('board_id',$boardId)
                ->wherePivot('order', '>=', $order)
                ->withPivot('order')
                ->get();

            foreach ($features as $featureStage){

                if($featureStage->id === $feature->id){

                    $pivotAttributes = ['order'=>$order];

                }else{
                    if($isOrderAsc){
                        $pivotAttributes = ['order'=>$featureStage->order + 1];
                    }else{
                        $pivotAttributes = ['order'=>$featureStage->order - 1];
                    }
                }

                $featureStage->stages()->updateExistingPivot($stage->id, $pivotAttributes);
                $featureStage->order = $pivotAttributes['order'];
                $featureStage->save();
            }
        }catch (\Exception $e){
            $isOk = false;
        }
        return $isOk;
    }

    public function createFeature(StoreFeatureRequest $request): JsonResponse{
        try {
            $this->authorize("create",Feature::class);

            $boardId = $request->board_id;
            $stageId = $request->stage_id;


            $board = Board::where('id', $boardId)
                ->where('status', $this->status)
                ->with(['stages'])
                ->first();

            if (!$board) {
                $r = CustomResponse::badRequest("No existe el tablero");
                return response()->json($r, $r->code);
            }

            $stage = $board->stages->where("id", $stageId)->first();

            if(!$stage){
                $r = CustomResponse::badRequest("El estado no pertenece a este tablero");
                return response()->json($r, $r->code);
            }

            $features = array_values(collect($stage->features)->filter(function ($feature) use ($boardId){
                $pivot = $feature->pivot;
                return $pivot->board_id === $boardId;
            })->toArray());

            $maxPivotOrder = 0;
            if(!empty($features)){
                $maxPivotOrder = collect($features)->max("pivot.order");
            }

            $description = $request->description ? $request->description : "";
            $due_date = $request->due_date ? Carbon::createFromFormat('Y-m-d H:i:s', $request->due_date) : null;

            $order = $maxPivotOrder + 1;

            $feature = Feature::create([
                'title'=>$request->title,
                'description'=>$description,
                'board_id'=>$board->id,
                'stage_id'=>$stage->id,
                'order'=>$order,
                'due_date'=>$due_date
            ]);

            $stage->features()->attach($feature->id,[
                'board_id'=>$board->id,
                'order'=>$order
            ]);

            $r = CustomResponse::ok("Ok");
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function changeOrderFeatureOrMovingToAnotherStage(StoreChangeOrderFeatureRequest $request, $featureId):JsonResponse{
        try {
            $this->authorize("changeOrder",Feature::class);

            $stageId = $request->stage_id;
            $newStageId = $request->new_stage_id;
            $newStage = null;


            $feature = Feature::where('id',$featureId)
                ->with(['stages'=>function ($query) use ($stageId){
                    $query->where('feature_stage.stage_id',$stageId)->first();
                }])
                ->first();

            if(!$feature){
                $r = CustomResponse::badRequest("No existe la funcionalidad");
                return response()->json($r, $r->code);
            }

            $currentOrderFeature = $feature->order;
            $newOrderFeature = $request->newOrder;

            $boardId = $feature->board_id;

            $board = Board::where('id', $boardId)
                ->where('status', $this->status)
                ->whereHas('stages')
                ->first();

            if(!$board){
                $r = CustomResponse::badRequest("No existe el tablero");
                return response()->json($r, $r->code);
            }

            $stageCurrent = $feature->stages->where(function ($stage) use ($boardId, $stageId){
                return ($stage->pivot->stage_id === $stageId) && ($stage->pivot->board_id === $boardId);
            })->first();

            $stagesBoard = $board->stages;

            if(!empty($stagesBoard)){
                $newStage = $stagesBoard->where('pivot.stage_id',$newStageId)->first();
            }

            if(!$newStage){
                $r = CustomResponse::badRequest("El feature que intentas cambiar debe ser un estado del grupo del tablero");
                return response()->json($r, $r->code);
            }

            DB::beginTransaction();

            if(($newStage->id === $stageCurrent->id) && ($newOrderFeature !== $currentOrderFeature)){

                $maxOrder = max($newOrderFeature, $currentOrderFeature);
                $minOrder = min($newOrderFeature, $currentOrderFeature);

                //ordena de forma vertial
                $isOrder = $this->orderVerticalFeaturesToStage($minOrder,$maxOrder,$feature,$stageCurrent,$boardId,$newOrderFeature,$currentOrderFeature);

                if(!$isOrder){
                    $r = CustomResponse::badRequest("Ocurrio un error en el servidor al ordenar las features");
                    return response()->json($r, $r->code);
                }

            }else if($newStage->id !== $stageCurrent->id){

                $feature->stages()->updateExistingPivot($stageCurrent->id,[
                    'stage_id'=>$newStage->id,
                    'board_id'=>$board->id,
                    'order'=>$newOrderFeature
                ]);

                $feature->stage_id = $newStage->id;
                $feature->board_id = $board->id;
                $feature->order = $newOrderFeature;
                $feature->save();

                //ordenar las features del stage que fue agregado la nueva feature
                $isOrder = $this->orderFeaturesWhenMovingToAnotherStage($newStage,$boardId,$newOrderFeature,$feature,true);

                if(!$isOrder){
                    DB::rollBack();
                    $r = CustomResponse::badRequest("Ocurrio un error en el servidor al ordenar las features del nuevo stage");
                    return response()->json($r, $r->code);
                }

                //ordenar las features del stage anterior
                $isOrder = $this->orderFeaturesWhenMovingToAnotherStage($stageCurrent,$boardId,$currentOrderFeature,$feature,false);

                if(!$isOrder){
                    DB::rollBack();
                    $r = CustomResponse::badRequest("Ocurrio un error en el servidor al ordenar las features del anterior stage");
                    return response()->json($r, $r->code);
                }

                /*$featuresNewStage = $newStage->features()
                    ->wherePivot('board_id',$boardId)
                    ->wherePivot('order', '>=', $newOrderFeature)
                    ->withPivot('order')
                    ->get();


                foreach ($featuresNewStage as $featureStage){

                    if($featureStage->id === $feature->id){

                        $pivotAttributes = ['order'=>$newOrderFeature];

                    }else{
                        $pivotAttributes = ['order'=>$featureStage->order + 1];
                    }

                    $featureStage->stages()->updateExistingPivot($newStage->id, $pivotAttributes);
                    $featureStage->order = $pivotAttributes['order'];
                    $featureStage->save();
                }

                $featuresOldStage = $stageCurrent->features()
                    ->wherePivot('board_id',$boardId)
                    ->wherePivot('order', '>=', $currentOrderFeature)
                    ->withPivot('order')
                    ->get();

                foreach ($featuresOldStage as $featureStage){

                    if($featureStage->id === $feature->id){

                        $pivotAttributes = ['order'=>$newOrderFeature];

                    }else{
                        $pivotAttributes = ['order'=>$featureStage->order - 1];
                    }

                    $featureStage->stages()->updateExistingPivot($stageCurrent->id, $pivotAttributes);
                    $featureStage->order = $pivotAttributes['order'];
                    $featureStage->save();
                }*/

            }
            DB::commit();
            $r = CustomResponse::ok("Ok");
            return response()->json($r);

        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            DB::rollBack();
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

}
