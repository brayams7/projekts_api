<?php

namespace App\Http\Controllers;

use App\Exceptions\AttachmentTypeNotFoundException;
use App\Http\Requests\AssignFeatureToUserRequest;
use App\Http\Requests\AttachmentRequest;
use App\Http\Requests\FeatureCommentRequest;
use App\Http\Requests\FeatureRequest;
use App\Http\Requests\StoreChangeOrderFeatureRequest;
use App\Http\Requests\StoreFeatureRequest;
use App\Models\Board;
use App\Models\CustomResponse;
use App\Models\Feature;
use App\Models\FeatureComment;
use App\Models\Stage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Ramsey\Uuid\Uuid;

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

    public function getDetailFeature(Request $request, $featureId):JsonResponse{
        try {
            $this->authorize("getDetail",Feature::class);

            $feature = Feature::where('id',$featureId)->first();

            $user = $request->user();

            if(!$feature){
                $r = CustomResponse::badRequest("El feature solicitado no exite");
                return response()->json($r, $r->code);
            }

            $board = Board::where('id',$feature->board_id)
                ->where('status',$this->status)
                ->first();

            if(!$board){
                $r = CustomResponse::badRequest("Error en los datos proporcionados");
                return response()->json($r, $r->code);
            }

            $workspace = $board->workspace;

            $workspaceController =  new WorkspaceController();

            $listOfUsersAddedToTheWorkspace = $workspaceController->getMembersInWorkspace($workspace);

            $isMemberInWorkspace = $listOfUsersAddedToTheWorkspace->contains(function ($member) use($user){
                return  $member->id === $user->id;
            });

            if(!$isMemberInWorkspace){
                $r = CustomResponse::forbidden("Necesitas ser miembro del espacio de trabajo al que pertenece esta funcionalidad");
                return response()->json($r, $r->code);
            }

            //Obtener los usuarios asignados a este feature
            $listAssignedUsers = self::listOfUsersAssignedToTheFunctionality($feature);


            $stages = $board->stages()
                ->wherePivot('board_id', '>=', $board->id)
                ->orderByPivot('order', 'asc')
                ->get();


            $mapListStages = $stages->map(function ($stage){
                $order = $stage->pivot->order;
                $stage['order'] = $order;
                unset($stage->pivot);

                return $stage;
            });

            $feature->stages = $mapListStages;
            $feature->board = $board;
            $feature->list_of_users_added_to_the_workspace = $listOfUsersAddedToTheWorkspace;
            $feature->list_of_users_assigned = $listAssignedUsers;

            $r = CustomResponse::ok($feature);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function listCommentsFeature(Request $request, $featureId):JsonResponse{
        try {
            $this->authorize("getDetail",Feature::class);

            $feature = Feature::where('id',$featureId)->first();

            if(!$feature){
                $r = CustomResponse::badRequest("El feature solicitado no exite");
                return response()->json($r, $r->code);
            }

            $comments = FeatureComment::where('feature_id', $feature->id)
                ->with(['user','attachments'])
                ->orderBy('created_at','desc')
                ->cursorPaginate(15);

            $comments->hasMorePages();
            $comments->count();

            $r = CustomResponse::ok($comments);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function createCommentFeature(FeatureCommentRequest $request):JsonResponse{
        try {
            $this->authorize("createComment",Feature::class);

            $userId = $request->user_id;
            $featureId = $request->feature_id;
            $comment= ($request->comment || isset($request->comment)) ? $request->comment : "";
            $file = $request->hasFile('file') ? $request->file('file') : null;

            $user = User::where('id', $userId)
                ->where('status', $this->status)
                ->first();

            $feature = Feature::where('id',$featureId)->first();

            if(!$feature || !$user){
                $r = CustomResponse::badRequest("El feature solicitado no exite");
                return response()->json($r, $r->code);
            }

            $newComment = FeatureComment::create([
                'user_id'=>$user->id,
                'feature_id'=>$feature->id,
                'comment'=>$comment
            ]);

            if($file){

                $attachmentController = new AttachmentController();

                $attachment = $attachmentController->createToAWS($file);

                if(!$attachment){
                    $r = CustomResponse::intertalServerError("No se pudo persistir el archivo adjunto");
                    return response()->json($r, $r->code);
                }

                $newComment->attachments()->attach($attachment->id);

            }

            $r = CustomResponse::ok($newComment);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function addAttachmentToFeature(AttachmentRequest $request, $featureId):JsonResponse{
        try {
            $this->authorize("addAttachment",Feature::class);

            $feature = Feature::where('id',$featureId)->first();

            if(!$feature || !$request->hasFile("file")){
                $r = CustomResponse::badRequest("Error en las credenciales");
                return response()->json($r, $r->code);
            }

            $file = $request->file('file');

            $attachmentController = new AttachmentController();

            $attachment = $attachmentController->createToAWS($file);

            if(!$attachment){
                $r = CustomResponse::badRequest("Ocurrio un error en el servidor");
                return response()->json($r, $r->code);
            }

            $feature->attachments()->attach($attachment->id);

            $r = CustomResponse::ok("OK");
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (AttachmentTypeNotFoundException $e) {

            $r = CustomResponse::badRequest("Tipo de archivo no aceptado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function listAttachmentsOfFeature(Request $request, $featureId):JsonResponse{
        try {
            $this->authorize("getDetail",Feature::class);

            $feature = Feature::where('id',$featureId)
                ->with(['attachments'=>function ($query){
                    $query->orderBy('attachments.created_at','asc')
                        ->with(['attachmentType']);
                }])
                ->first();

            if(!$feature){
                $r = CustomResponse::badRequest("El feature solicitado no exite");
                return response()->json($r, $r->code);
            }


            $attachments = $feature->attachments;

            $mapAttachments = $attachments->map(function ($attachment){
                $attachment->feature_id = $attachment->pivot->feature_id;
                $attachment->name = pathinfo(basename($attachment->url),PATHINFO_FILENAME);
                unset($attachment->pivot);
                return $attachment;
            });

            $r = CustomResponse::ok($mapAttachments);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function deleteAttachmentOfFeature(Request $request, $featureId, $attachmentId):JsonResponse{
        try {
            //$this->authorize("deleteAttachment",Feature::class);

            $feature = Feature::where('id',$featureId)->first();

            if(!$feature){
                $r = CustomResponse::badRequest("Error en las credenciales");
                return response()->json($r, $r->code);
            }

            $attachment = $feature->attachments()
                ->wherePivot('attachment_id', $attachmentId)
                ->first();

            if(!$attachment){
                $r = CustomResponse::badRequest("El attachment no exite");
                return response()->json($r, $r->code);
            }

            $url = $attachment->url;

            $attachmentController = new AttachmentController();

            $isDelete = $attachmentController->delete($url);

            if ($isDelete) {
                $attachment->delete();
            }

            $r = CustomResponse::ok("OK");
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (AttachmentTypeNotFoundException $e) {

            $r = CustomResponse::badRequest("Tipo de archivo no aceptado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    private function listOfUsersAssignedToTheFunctionality(Feature $feature){
        try {
            return $feature->assignedUsers()->get()
                ->map(function ($assignment){
                    $isWatcher = $assignment->pivot->is_watcher;
                    $assignment->is_watcher = $isWatcher;
                    unset($assignment->pivot);
                    return $assignment;
                });
        }catch (\Exception $e){
            return [];
        }
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

    public function assignFeatureToUser(AssignFeatureToUserRequest $request):JsonResponse{
        try {
            $this->authorize("assignUserToFeature",Feature::class);

            $featureId = $request->feature_id;
            $userId = $request->user_id;
            $isWatcher = $request->is_watcher;

            $feature = Feature::where('id',$featureId)->first();

            $user = User::where("id", $userId)
                ->where('status', $this->status)
                ->first();

            if(!$feature || !$user){
                $r = CustomResponse::badRequest("Error en las credenciales");
                return response()->json($r, $r->code);
            }

            $listUserAssigned = self::listOfUsersAssignedToTheFunctionality($feature);
            $isExistUser = $listUserAssigned->first(function ($assignment) use ($user){
                return $assignment->id === $user->id;
            });

            if(!$isExistUser) {
                $pivotAttributes = ['is_watcher'=>$isWatcher];
                $feature->assignedUsers()->attach($user->id, $pivotAttributes);
            }

            $r = CustomResponse::ok("ok");
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function deleteUserToFeature(AssignFeatureToUserRequest $request):JsonResponse{
        try {
            $this->authorize("deleteUserToFeature",Feature::class);

            $featureId = $request->feature_id;
            $userId = $request->user_id;

            $feature = Feature::where('id',$featureId)->first();

            $user = User::where("id", $userId)
                ->where('status', $this->status)
                ->first();

            if(!$feature || !$user){
                $r = CustomResponse::badRequest("Error en las credenciales");
                return response()->json($r, $r->code);
            }

            /*$listUserAssigned = self::listOfUsersAssignedToTheFunctionality($feature);

            $isExistUser = $listUserAssigned->first(function ($assignment) use ($user){
                return $assignment->user_id > $user->id;
            });*/

            /*if($isExistUser) {

            }*/
            $feature->assignedUsers()->detach($user->id);

            $r = CustomResponse::ok("ok");
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    /**
     * @param AssignFeatureToUserRequest $request
     * @return JsonResponse
     *
     * Context:
     *  este controlador cambia la visibilidad de un usuario a una funcionalidad al que fue
     *  asignado
     */
    public function changeVisibilityFromUserToAFeature(AssignFeatureToUserRequest $request): JsonResponse
    {
        try {
            $this->authorize("changeVisibilityFromUserToAFeaturePolicy",Feature::class);

            $featureId = $request->feature_id;
            $userId = $request->user_id;
            $isWatcher = $request->is_watcher;

            $feature = Feature::where('id',$featureId)->first();

            $user = User::where("id", $userId)
                ->where('status', $this->status)
                ->first();

            if(!$feature || !$user){
                $r = CustomResponse::badRequest("Error en las credenciales");
                return response()->json($r, $r->code);
            }

            /*$listUserAssigned = self::listOfUsersAssignedToTheFunctionality($feature);

            $isExistUser = $listUserAssigned->first(function ($assignment) use ($user){
                return $assignment->user_id > $user->id;
            });*/

            $pivotAttributes = ['is_watcher'=>$isWatcher];
            $feature->assignedUsers()->updateExistingPivot($user->id, $pivotAttributes);

            $r = CustomResponse::ok("ok");
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }


    /**
     * @param FeatureRequest $request
     * @return JsonResponse
     *
     * Context:
     *  editar una funcionalidad
     */
    public function updateFeature(FeatureRequest $request, $featureId):JsonResponse{
        try {
            $this->authorize("updateFeaturePolicy",Feature::class);

            $feature = Feature::where('id',$featureId)->first();

            if(!$feature){
                $r = CustomResponse::badRequest("Error en las credenciales");
                return response()->json($r, $r->code);
            }

            $title = $request->title ? $request->title : $feature->title;
            //$dueDate = $request->due_date ? Carbon::parse($request->due_date)->format('Y-m-d H:i:s') : $feature->due_date;
            $dueDate = $request->due_date ? Carbon::createFromTimestamp($request->due_date)->toDateTimeString() : $feature->due_date;
            $description = $request->description ? trim($request->description) : $feature->description;

            $feature->title = $title;
            $feature->description = $description;
            $feature->due_date = $dueDate;

            $feature->save();

            $r = CustomResponse::ok($feature);
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
