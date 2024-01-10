<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackingRequest;
use App\Models\CustomResponse;
use App\Models\Task;
use App\Models\Traking;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class TrakingController extends Controller
{
    public function addTrackingToTask(TrackingRequest $request, $taskId): JsonResponse
    {
        try {
            $this->authorize("create",Traking::class);

            $task = Task::where('id', $taskId)->first();

            $user = $request->input('user');

            if(!$task || !$user){
                $r = CustomResponse::badRequest("Error en los datos");
                return response()->json($r, $r->code);
            }

            $isUserAssignedToTheTask = $task->assignedUsers()->get()->first(function ($item) use ($user){
                return $item->id === $user->id;
            });

            if(!$isUserAssignedToTheTask){
                $r = CustomResponse::badRequest("El usuario debe ser asignado a esta tarea.");
                return response()->json($r, $r->code);
            }

            $description = $request->input('description',null);
            $hours = $request->input('hours');
            $minutes = $request->input('minutes');
            $full_minutes = $request->input('full_minutes');
            $date = $request->input('date', null);
            $day = $request->input('day', null);
            $month = $request->input('month', null);
            $year = $request->input('year', null);

            $tracking = Traking::create([
                'description'=>$description,
                'hours'=>$hours,
                'minutes'=>$minutes,
                'day'=>$day,
                'full_minutes'=>$full_minutes,
                'date'=>$date,
                'month'=>$month,
                'year'=>$year,
                'created_at'=>strtotime('now'),
                'task_id'=>$task->id,
                'user_id'=>$user->id
            ]);

            $tracking->user = $tracking->user->get();

            $r = CustomResponse::ok($tracking);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function listTrackingByTask(Request $request, $taskId): JsonResponse
    {
        try {
            $this->authorize("viewAny",Traking::class);

            $listTracking = Task::with(['listTracking.user'])
                    ->where('id', $taskId)
                    ->first();

            $r = CustomResponse::ok($listTracking);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function deleteTrackingByTask(Request $request, $trackingId): JsonResponse
    {
        try {
            $tracking = Traking::where('id', $trackingId)->first();
            if(!$tracking){
                $r = CustomResponse::badRequest("El elemento no exite.");
                return response()->json($r, $r->code);
            }
            $this->authorize("delete",$tracking);

            $tracking->delete();

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

    public function updateTrackingByTask(TrackingRequest $request, $trackingId): JsonResponse
    {
        try {
            $tracking = Traking::where('id', $trackingId)->first();

            if(!$tracking){
                $r = CustomResponse::badRequest("El elemento no exite.");
                return response()->json($r, $r->code);
            }

            $this->authorize("update",$tracking);

            $description = $request->input('description',"");
            $hours = $request->input('hours', 0);
            $minutes = $request->input('minutes', 0);
            $full_minutes = $request->input('full_minutes', 0);
            $date = $request->input('date', null);
            $day = $request->input('day', null);
            $month = $request->input('month', null);
            $year = $request->input('year', null);

            $tracking->description = $description;
            $tracking->hours = $hours;
            $tracking->minutes = $minutes;
            $tracking->full_minutes = $full_minutes;
            $tracking->date = $date;
            $tracking->day = $day;
            $tracking->month = $month;
            $tracking->year = $year;

            $tracking->save();

            $tracking->user = $tracking->user->get();

            $r = CustomResponse::ok($tracking);
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
