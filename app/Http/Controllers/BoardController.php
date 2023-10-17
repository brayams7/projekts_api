<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardRequest;
use App\Models\Board;
use App\Models\CustomResponse;
use App\Models\Stage;
use App\Models\User;
use App\Models\Workspace;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Constants\AppConstants;
use App\Constants\Constants;
use Illuminate\Support\Facades\Storage;

class BoardController extends Controller
{

    protected int $status = 1;

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $listBoards = Board::where('status', 1)
            ->orderByRaw('updated_at - created_at DESC')
            ->get();

        foreach ($listBoards as $board) {
            $board->workspace;
        }

        $r = CustomResponse::ok($listBoards);
        return response()->json($r);
    }

    /**
     * Display a listing of the resource.
     * @param  int  $idWorkspace
     * @return JsonResponse
     */
    public function getBoardsByWorkspace(Request $request, $workspaceId): JsonResponse
    {

        try {
            $per_page = 10;

            if($request->has('per_page'))  $per_page=$request->per_page;

            $workspace = Board::where('status', 1)
                ->where('workspace_id', $workspaceId)
                ->paginate($per_page);

            // $data = [
            //     'current_page'=>$workspace->current_page,
            //     'data'=>$workspace->data,
            //     'per_page'=>$workspace->per_page,
            //     'total'=>$workspace->total,
            //     'next_page_url'=>$workspace->next_page_url,
            //     'prev_page_url'=>$workspace->prev_page_url
            // ];

            $r = CustomResponse::ok($workspace);
            return response()->json($r);

        } catch (Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }

    public function getBoardsByUserAndWorkspace(Request $request, $idWorkspace, $userId): JsonResponse
    {

        try {
            $per_page = 10;

            if($request->has('per_page'))  $per_page=$request->per_page;

            $user = User::where("id", $userId)
                ->where('status', $this->status)
                ->first();

            $workspace = Workspace::where('id', $idWorkspace)
                ->where('status', $this->status)
                ->first();

            if(!$user || !$workspace){
                $r = CustomResponse::badRequest("Error en los datos");
                return response()->json($r, $r->code);
            }

            /*
             * Validar si el usuario es miebro o el dueño del espacio de trabajo
             * */
            $workspaceController = new WorkspaceController();

            $isMemberOfTheWorkspace = $workspaceController->validateIsMemberInWorkspace($user,$workspace);

            if($isMemberOfTheWorkspace){
                $boards = Board::where('status', 1)
                    ->where('workspace_id', $idWorkspace)
                    ->paginate($per_page);
                $r = CustomResponse::ok($boards);
                return response()->json($r);
            }

            $r = CustomResponse::badRequest("No es miembro o dueño de este recurso");
            return response()->json($r);

        } catch (Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBoardRequest $request
     * @return JsonResponse
     */
    public function store(StoreBoardRequest $request): JsonResponse
    {
        try {
            $this->authorize("create", Board::class);

            $user = User::where('id', $request->user_id)
                ->where('status', 1)
                ->first();

            $workspace = Workspace::where('id', $request->workspace_id)
                ->where('status', 1)
                ->first();


            if (!$user || !$workspace) {
                $r = CustomResponse::badRequest("Error en los datos proporcionados");
                return response()->json($r, $r->code);
            }

            //validate if user is member in workspace of board

            $workspaceController = new WorkspaceController();
            $isMember = $workspaceController->validateIsMemberInWorkspace($user,$workspace);

            if(!$isMember){
                $r = CustomResponse::badRequest("No tienes acceso a este board");
                return response()->json($r, $r->code);
            }

            /*if($request->hasFile("bg_image")){
                $file = $request->file("bg_image");
                $bgImageName = Str::random(32) . "." . $file->getClientOriginalExtension();
                $mime = $file->getClientMimeType();
                $r = CustomResponse::ok($mime);
                return response()->json($bgImageName);
            }*/

            $bgImageName = $request->bg_image ? Str::random(32) . "." . $request->bg_image->getClientOriginalExtension() : "";
            $bgColor = $request->bg_color ? $request->bg_color : Constants::DEFAULT_COLOR;
            if ($bgImageName) {
                Storage::disk(Constants::NAME_STORAGE)->put($bgImageName, file_get_contents($request->bg_image));
            }

            $isDefaultStages = (int) $request->have_default_stages;

            DB::beginTransaction();

            $board = Board::create([
                'name' => $request->name,
                'description' => $request->description,
                'bg_color' => $bgColor,
                'bg_img' => $bgImageName,
                'user_id' => $user->id,
                'workspace_id' => $workspace->id,
                'status' => $this->status
            ]);

            $order = 1;
            if($isDefaultStages === 1){
                $stages = Stage::where('is_default', 1)->get();
                foreach ($stages as $stage){
                    $board->stages()->attach($stage->id,['order'=>$order]);
                    $order++;
                }
            }
            DB::commit();


            $r = CustomResponse::ok($isMember);
            return response()->json($r);

        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);
        } catch (Exception $e) {
            DB::rollBack();
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }

    public function getBoardAndStages(Request $request, int $boardId): JsonResponse
    {
        try {
            $board = Board::where('id', $boardId)
                ->where('status', $this->status)
                ->with(['stages' => function ($query) {
                    $query->orderBy('board_stage.order');
                }])
                ->first();

            if(!$board){
                $r = CustomResponse::badRequest("El tablero no exite");
                return response()->json($r, $r->code);
            }

            $board->stages = $board->stages->map(function ($stage) {
                $stage->order = $stage->pivot->order; // Agrega el atributo 'order'
                unset($stage->pivot); // Elimina el elemento 'pivot'
                return $stage;
            });

        }catch (\Exception $e){
            $r = CustomResponse::badRequest("Error en los datos proporcionados");
            return response()->json($r, $r->code);
        }

        $r = CustomResponse::ok($board);
        return response()->json($r);
    }

    public function getDetailBoard(Request $request, $boardId): JsonResponse
    {

        try {
            $this->authorize("viewAny", Board::class);

            $board = Board::where('id', $boardId)
                ->where('status',$this->status)
                ->with(['stages' => function ($query) use ($boardId){
                    $query->orderBy('board_stage.order')
                        ->with(['features' => function ($query) use ($boardId){
                            $query->where('feature_stage.board_id',$boardId)
                                ->orderBy('feature_stage.order')
                                ->orderBy('due_date');
                        }]);
                }])
                ->first();
            if(!$board){
                $r = CustomResponse::badRequest("El tablero no exite");
                return response()->json($r, $r->code);
            }

            $workspace = Workspace::where('id', $board->workspace_id)
                ->where('status', $this->status)
                ->first();

            $user = User::where("id", $workspace->user_id)
                ->where('status', $this->status)
                ->first();

            if (!$user || !$workspace) {
                $r = CustomResponse::badRequest([
                    "data" => "Error en las credenciales"
                ]);
                return response()->json($r, $r->code);
            }

            //Obtener los usuarios o miebros de este board
            $workspaceController = new WorkspaceController();

            $members = $workspaceController->getMembersInWorkspace($workspace);

            $board->members = $members;

            $board->stages = $board->stages->map(function ($stage) {
                $stage->order = $stage->pivot->order; // Agrega el atributo 'order'
                unset($stage->pivot); // Elimina el elemento 'pivot'

                $stage->features = $stage->features->map(function ($feature){
                    $feature->order = $feature->pivot->order;
                    $feature->board_id = $feature->pivot->board_id;
                    unset($feature->pivot);
                    return $feature;
                });

                return $stage;
            });


            $r = CustomResponse::ok($board);
            return response()->json($r);
        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (Exception $e){
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreBoardRequest $request
     * @param $boardId
     * @return JsonResponse
     */
    public function updateBoard(StoreBoardRequest $request, $boardId): JsonResponse
    {
        try {

            $this->authorize("update", Board::class);

            $board = Board::where('id', $boardId)
                ->where('status', $this->status)
                ->first();

            if (!$board) {
                $r = CustomResponse::badRequest("No existe el tablero");
                return response()->json($r, $r->code);
            }

            $user = User::where('id', $request->user_id)
                ->where('status', $this->status)
                ->first();

            $workspace = Workspace::where('id', $request->workspace_id)
                ->where('status', $this->status)
                ->first();


            if (!$user || !$workspace) {
                $r = CustomResponse::badRequest("Error en los datos proporcionados");
                return response()->json($r, $r->code);
            }

            //Obtener los usuarios o miebros de este board
            //$workspaceController = new WorkspaceController();

            //$members = $workspaceController->getMembersInWorkspace($workspace);
            //$board->members = $members;

            $board->name = $request->name;
            $board->description = $request->description;
            $board->user_id = $user->id;
            $board->workspace_id = $workspace->id;
            $board->bg_color = $request->bg_color;

            if (isset($request->bg_image) && $request->bg_image) {
                $storage = Storage::disk(Constants::NAME_STORAGE);

                if ($storage->exists($board->bg_img))
                    $storage->delete($board->bg_img);

                //imageName
                $bgImageName = Str::random(32) . "." . $request->bg_image->getClientOriginalExtension();
                $board->bg_img = $bgImageName;

                $storage->put($bgImageName, file_get_contents($request->bg_image));
            }

            $board->save();
            $r = CustomResponse::ok($board);
            return response()->json($r);

        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        } catch (Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor.");
            return response()->json($r, $r->code);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
