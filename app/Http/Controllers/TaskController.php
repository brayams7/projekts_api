<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeOrderTaskRequest;
use App\Http\Requests\TaskRequest;
use App\Models\Board;
use App\Models\CustomResponse;
use App\Models\Feature;
use App\Models\Tag;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Array_;
use \Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class TaskController extends Controller
{
    public function createTask(TaskRequest $request):JsonResponse{
        try {
            $this->authorize("create",Task::class);

            $feature = Feature::where('id',$request->feature_id)->first();

            if(!$feature){
                $r = CustomResponse::badRequest("El feature no existe");
                return response()->json($r, $r->code);
            }

            $title =  $request->title;
            $description = $request->description ? $request->description : '';
            $createdAt = strtotime('now');
            $tags = $request->tags_id;

            $startAt = $request->starts_at ? Carbon::createFromTimestamp($request->starts_at)->toDateTimeString() : null;
            $dueDate = $request->due_date ? Carbon::createFromTimestamp($request->due_date)->toDateTimeString() : null;

            if(($startAt && $dueDate) && ($dueDate < $startAt)){
                $r = CustomResponse::badRequest("La fecha de expiración debe ser mayor a la fecha de inicio.");
                return response()->json($r, $r->code);
            }

            $beforeTask = $feature->tasks()->latest()->first();

            DB::beginTransaction();


            $task = Task::create([
                'title'=>$title,
                'feature_id'=>$feature->id,
                'description'=>$description,
                'task_before'=>$beforeTask ? $beforeTask->id : $beforeTask,
                'created_at'=>$createdAt,
                'starts_at'=>$startAt,
                'due_date'=>$dueDate
            ]);

            if ($beforeTask) {
                //$uuid = Uuid::fromString($task->id);

                self::assignAfterTask($beforeTask,$task->id);
            }

            if (!empty($tags)) {

                $tags = Tag::whereIn('id', $tags)
                    ->select('id')
                    ->get();

                $uuidsTags = $tags->map(function ($tag) {
                    return $tag->id;
                });

                self::assignTagsToFeature($task, $uuidsTags);
            }

            DB::commit();

            //$firstTask = $feature->tasks()->first();


            $r = CustomResponse::ok($task);
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

    public function listTaskOfFeature(Request $request, $featureId):JsonResponse{
        try {
            $this->authorize("create",Task::class);

            $feature = Feature::where('id',$featureId)
                ->with([
                    'tasks'=> function ($query) {
                        $query->orderBy('created_at','asc')
                            ->with([
                                'tags'
                            ]);
                    }
                ])->first();

            if(!$feature){
                $r = CustomResponse::badRequest("El feature no existe");
                return response()->json($r, $r->code);
            }

            $tasks= $feature->tasks;

            $r = CustomResponse::ok($tasks);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }


    public function updateTask(TaskRequest $request, $taskId):JsonResponse{
        try {
            $this->authorize("update",Task::class);


            $task = Task::where('id',$taskId)->first();

            if(!$task){
                $r = CustomResponse::badRequest("La terea no existe");
                return response()->json($r, $r->code);
            }
            $title = $request->title ? $request->title : $task->title;
            $dueDate = $request->due_date ? Carbon::createFromTimestamp($request->due_date)->toDateTimeString() : $task->due_date;
            $calculatedTime = $request->calculated_time ? Carbon::createFromTimestamp($request->calculated_time)->toDateTimeString() : $task->calculated_time;
            $startsAt = $request->starts_at ? Carbon::createFromTimestamp($request->starts_at)->toDateTimeString() : $task->starts_at;

            $description = $request->description ? trim($request->description) : $task->description;

            $task->title=$title;
            $task->description = $description;
            $task->due_date = $dueDate;
            $task->calculated_time = $calculatedTime;
            $task->starts_at = $startsAt;

            $task->save();

            $r = CustomResponse::ok($task);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function changeAfterOrBeforeTask(ChangeOrderTaskRequest $request):JsonResponse{
        try {
            $this->authorize("changeOrderTask",Task::class);

            $taskId = $request->task_id;
            $newBeforeTaskId = $request->new_before_task ? $request->new_before_task : null;
            $newAfterTaskId = $request->new_after_task ? $request->new_after_task : null;

            $task = Task::where('id',$taskId)->first();
            $newBeforeTask = Task::where('id',$newBeforeTaskId)->first();
            $newAfterTask = Task::where('id',$newAfterTaskId)->first();

            if(!$task || (!$newBeforeTask && !$newAfterTask)){
                $r = CustomResponse::badRequest("Error en los datos.");
                return response()->json($r, $r->code);
            }

            if(
                (!$newBeforeTask || $task->feature_id === $newBeforeTask->feature_id) &&
                (!$newAfterTask || $task->feature_id === $newAfterTask->feature_id)
            ){
                $currentBeforeTask = Task::where('id',$task->task_before)->first();
                $currentAfterTask = Task::where('id',$task->task_after)->first();

                DB::beginTransaction();

                //Modificando las referencias de la tarea anterio y la posterior de la tarea actual.

                if($currentBeforeTask){
                    $currentBeforeTask->task_after = $task->task_after;
                    $currentBeforeTask->save();
                }

                if ($currentAfterTask) {
                    $currentAfterTask->task_before = $task->task_before;
                    $currentAfterTask->save();
                }



                //Modificando las nuevas referencias de la tarea.

                if ($newBeforeTask) {
                    $newBeforeTask->task_after = $task->id;
                    $task->task_before = $newBeforeTask->id;
                    $newBeforeTask->save();
                }else{
                    $task->task_before = null;
                }

                if ($newAfterTask) {
                    $newAfterTask->task_before = $task->id;
                    $task->task_after = $newAfterTask->id;
                    $newAfterTask->save();

                }else{
                    $task->task_after = null;
                }
                $task->save();

                DB::commit();
                $r = CustomResponse::ok($task);
                return response()->json($r);
            }else{
                $r = CustomResponse::badRequest("La tareas que deseas modificar deben de estar en el mismo grupo de la funcionalidad.");
                return response()->json($r, $r->code);
            }

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            echo $e;
            DB::rollBack();
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    protected function assignBeforeTask(Task $task, Uuid $uuid):bool{
        $task->task_before = $uuid;
        return $task->save();
    }

    protected function assignAfterTask(Task $task, $uuid):bool{
        $task->task_after = $uuid;
        return $task->save();
    }

    protected function assignTagsToFeature(Task $task, array $uuidsTags):bool{
        $band = true;

        $listMapUuids = collect($uuidsTags)->filter(function ($item){
            return Str::isUuid($item);
        })->toArray();

        try {

            $task->tags()->attach($listMapUuids);

        } catch (\Exception $exception) {
            $band =  false;
        }

        return $band;
    }
}
