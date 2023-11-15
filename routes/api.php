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
use \App\Http\Controllers\AttachmentController;
use \App\Http\Controllers\TaskController;
use \App\Http\Controllers\TagController;
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
    Route::get('/getDetailFeature/{boardId}', 'getDetailFeature')->middleware('authorization');
    Route::get('/listFeaturesByStage/{boardId}', 'getFeaturesByStage')->middleware('authorization');
    Route::post('/createFeature', 'createFeature')->middleware('authorization');
    Route::put('/updateFeature/{featureId}', 'updateFeature')->middleware('authorization');
    Route::put('/changeOrderFeatureOrMovingToAnotherStage/{featureId}', 'changeOrderFeatureOrMovingToAnotherStage')->middleware('authorization');


    Route::post('/createCommentFeature', 'createCommentFeature')->middleware('authorization');
    Route::get('/listCommentsFeature/{featureId}', 'listCommentsFeature')->middleware('authorization');

    Route::post('/addAttachmentToFeature/{featureId}', 'addAttachmentToFeature')->middleware('authorization');
    Route::get('/listAttachmentsOfFeature/{featureId}', 'listAttachmentsOfFeature')->middleware('authorization');
    Route::delete('/deleteAttachment/{featureId}/{attachmentId}', 'deleteAttachmentOfFeature');


    Route::post('/assignFeatureToUser', 'assignFeatureToUser')->middleware('authorization');
    Route::post('/deleteUserToFeature', 'deleteUserToFeature')->middleware('authorization');

    Route::post('/changeVisibilityFromUserToAFeature', 'changeVisibilityFromUserToAFeature')->middleware('authorization');

});

//Tasks

Route::controller(TaskController::class)->group(function (){
    Route::post('/createTask', 'createTask')->middleware('authorization');
    Route::post('/addChildTask/{taskId}', 'addChildTask')->middleware('authorization');
    Route::get('/listTaskOfFeature/{featureId}', 'listTaskOfFeature')->middleware('authorization');
    Route::get('/listChildrenTask/{taskId}', 'listChildrenTask')->middleware('authorization');
    Route::put('/updateTask/{taskId}', 'updateTask')->middleware('authorization');
    Route::post('/changeAfterOrBeforeTask', 'changeAfterOrBeforeTask')->middleware('authorization');
    //Route::post('/assignTaskToUserTest', 'assignTaskToUserTest')->middleware('authorization');
});

//Attachment

Route::controller(AttachmentController::class)->group(function (){
    Route::get('/downloadAttachment/{attachmentId}', 'downloadAttachment')->middleware('authorization');
    Route::get('/downloadAttachmentInAWS/{attachmentId}', 'downloadAttachmentInAWS')->middleware('authorization');
});

//tags

Route::controller(TagController::class)->group(function (){
    Route::post('/createTag', 'createTag')->middleware('authorization');
    Route::get('/listTags', 'listTags')->middleware('authorization');
    Route::put('/updateTag/{tagId}', 'updateTag')->middleware('authorization');
    Route::delete('/deleteTag/{tagId}', 'deleteTag')->middleware('authorization');
});


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
