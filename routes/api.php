<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceTypeController;
use App\Http\Controllers\StageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\FeatureController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::post('/auth/login', [LoginController::class, 'login']);
// Route::get('/auth/logout', [LoginController::class, 'logout']);
// Route::get('/auth/validate_session_token', [LoginController::class, 'validate_token']);

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/refresh_token', [AuthController::class, 'refreshToken']);


Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index')->middleware('authorization');
    Route::get('/users/searchUsersByEmailOrUsername', 'searchUsersByEmailOrUsername')->middleware('authorization');
});

//Workpaces types
Route::controller(WorkspaceTypeController::class)->group(function(){
    Route::get('/workpaces_types', 'index')->middleware('authorization');
    Route::post('/workpaces_types', 'store')->middleware('authorization');
});

//Workpaces
Route::controller(WorkspaceController::class)->group(function(){
    Route::get('/workpaces', 'index')->middleware('authorization');
    Route::post('/workpaces', 'store')->middleware('authorization');
    Route::put('/workpaces/{workspaceId}', 'update')->middleware('authorization');
    Route::get('/workpace/{workspaceId}', 'getWorkspaceByID')->middleware('authorization');
    Route::get('/workpaces/user/{id}', 'getWorkspacesUser')->middleware('authorization');
    Route::get('/workpaces/boards/user/{id}', 'getWorkspacesAndBoardByUser')->middleware('authorization');
    Route::get('/workpaces/userAndMembers/{userId}', 'getWorkspacesByUserandMembers')->middleware('authorization');

    Route::post('/workspace/inviteMemberToWorkspace/{workspaceId}', 'sendInvitationEmailToWorkspace')->middleware('authorization');
    Route::post('/workspace/acceptInvitationToWorkspace/', 'acceptInvitationToWorkspace');
});

//Boards
Route::controller(BoardController::class)->group(function(){Route::get('/boards', 'index')->middleware('authorization');
    Route::get('/boards/{id}', 'getBoard')->middleware('authorization');
    Route::post('/boards', 'store')->middleware('authorization');
    Route::post('/board/update/{boardId}', 'updateBoard')->middleware('authorization');
    Route::get('/boards/{idWorkspace}/{userId}', 'getBoardsByUserAndWorkspace')->middleware('authorization');
    Route::get('/boards/workspace/{idWorkspace}', 'getBoardsByWorkspace')->middleware('authorization');
    Route::get('/getBoardAndStages/{boardId}', 'getBoardAndStages')->middleware('authorization');
    Route::get('/getDetailBoard/{boardId}', 'getDetailBoard')->middleware('authorization');
});

//Stages

Route::controller(StageController::class)->group(function (){
    //Route::get('/stagesByBoard/{boardId}', 'getListStagesByBoard')->middleware('authorization');
    Route::post('/createStageByAssignToBoard/{boardId}', 'createStageAndAssignToBoard')->middleware('authorization');
    Route::post('/changeOrderStagesByBoard/{boardId}', 'changeOrderStagesByBoard')->middleware('authorization');
    Route::post('/updateOrderStagesByBoard/{boardId}', 'updateOrderStagesByBoard')->middleware('authorization');
    Route::put('/updateStage/{stageId}', 'updateStage')->middleware('authorization');
});


//features
Route::controller(FeatureController::class)->group(function (){
    //Route::get('/stagesByBoard/{boardId}', 'getListStagesByBoard')->middleware('authorization');
    Route::get('/listFeaturesByStage/{boardId}', 'getFeaturesByStage')->middleware('authorization');
    Route::post('/createFeature', 'createFeature')->middleware('authorization');
    Route::put('/changeOrderFeatureOrMovingToAnotherStage/{featureId}', 'changeOrderFeatureOrMovingToAnotherStage')->middleware('authorization');
});



// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
