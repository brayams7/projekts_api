<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Models\CustomResponse;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceType;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;

class WorkspaceController extends Controller
{

    protected int $status = 1;


    public function getWorkspaceByID($workspaceId): JsonResponse
    {

        try {

            $this->authorize("viewAny",Workspace::class);

            $workspace = Workspace::where('id',$workspaceId)
                ->where('status', $this->status)
                ->first();

            $user = User::where("id", $workspace->user_id)
                ->where('status', $this->status)
                ->first();

            if (!$user) {
                $r = CustomResponse::badRequest([
                    "data" => "Error en las credenciales"
                ]);
                return response()->json($r, $r->code);
            }

            $members = $this->getMembersInWorkspace($workspace);

            $workspace->members = $members;

            $workspace['user'] = $user;

            $r = CustomResponse::ok($workspace);
            return response()->json($r);
        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);
        }catch (Exception $e){
            $r = CustomResponse::badRequest("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function getMembersInWorkspace(Workspace $workspace){
        $userOwner = $workspace->user;
        $rolUser = $userOwner->role;

        $userOwner['memberType'] = $rolUser->name;
        $userOwner['isOwner'] = true;
        unset($userOwner->role);

        $members = $workspace->members->map(function ($member){
            $roleMember = $member->role;

            $member['memberType'] = $roleMember->name;
            $member['isOwner'] = false;
            unset($member->role);
            return $member;
        });

        unset($workspace->members);

        $members[] = $userOwner;

        return $members;
    }

    public function validateIsMemberInWorkspace(User $userOwner, Workspace $workspace){
        return self::getMembersInWorkspace($workspace)->where('id', $userOwner->id)->first();
    }

    public function getWorkspacesUser(string $userId): JsonResponse
    {
        try {

            $this->authorize("viewAny",Workspace::class);

            $user = User::where("id", $userId)
                ->where('status', $this->status)
                ->first();

            if (!$user) {
                $r = CustomResponse::badRequest([
                    "data" => "Error en las credenciales"
                ]);
                return response()->json($r, $r->code);
            }



            $myWorkspaces = $user->workspaces()
                ->where('status',$this->status)
                ->orderBy('updated_at','ASC')
                ->get();


            foreach ($myWorkspaces as $workspace){

                $members = $workspace->members->map(function ($member) use ($user) {

                    $member['memberType'] = Constants::ROLE_TYPE_ADMIN;
                    $member['isOwner'] = false;
                    return $member;
                });

                $user['memberType'] = Constants::ROLE_TYPE_ADMIN;
                $user['isOwner'] = true;

                $members[] = $user;

                unset($workspace->members);

                $workspace->members = $members;
                $workspace['user'] = $user;
            }

            //OBTENIENDO TODOS LOS WORKSPACES DONDE SOY INVITADO

            $guestWorkspaces = $user->memberWorkspaces()
                ->where('status',$this->status)
                ->orderBy('updated_at','ASC')
                ->get();

            foreach ($guestWorkspaces as $workspace){
                $userOwner = $workspace->user;

                $members = $workspace->members->map(function ($member){
                    $member['memberType'] = Constants::ROLE_TYPE_ADMIN;
                    $member['isOwner'] = false;
                    return $member;
                });

                $userOwner['memberType'] = Constants::ROLE_TYPE_ADMIN;
                $userOwner['isOwner'] = true;

                $members[] = $userOwner;

                unset($workspace->members);

                $workspace->members = $members;
                $workspace['user'] = $userOwner;
            }

            $workspaces = [
                "myWorkspaces"=>$myWorkspaces,
                "guestWorkspaces"=>$guestWorkspaces
            ];

            $r = CustomResponse::ok($workspaces);
            return response()->json($r);
        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);
        }catch (Exception $e){
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }

    }

    /*public function getWorkspacesByUserandMembers($userId)
    {
        $user = User::where("id", $userId)
            ->where('status', $this->status)
            ->first();

        if (!$user) {
            $r = CustomResponse::badRequest([
                "data" => "Error en las credenciales"
            ]);
            return response()->json($r, $r->code);
        }

        $myWorkspaces = $user->workspaces()
            ->where('status',$this->status)
            ->orderBy('updated_at','ASC')
            ->get();


        foreach ($myWorkspaces as $workspace){

            $members = $workspace->members->map(function ($member) use ($user) {

                $member['memberType'] = Constants::MEMBER_TYPE_ADMIN;
                $member['isOwner'] = false;
                return $member;
            });

            $user['memberType'] = Constants::MEMBER_TYPE_ADMIN;
            $user['isOwner'] = true;

            $members[] = $user;

            unset($workspace->members);

            $workspace->members = $members;
            $workspace['user'] = $user;
        }

        //OBTENIENDO TODOS LOS WORKSPACES DONDE SOY INVITADO

        $guestWorkspaces = $user->memberWorkspaces()
            ->where('status',$this->status)
            ->orderBy('updated_at','ASC')
            ->get();

        foreach ($guestWorkspaces as $workspace){
            $userOwner = $workspace->user;

            $members = $workspace->members->map(function ($member){
                $member['memberType'] = Constants::MEMBER_TYPE_ADMIN;
                $member['isOwner'] = false;
                return $member;
            });

            $userOwner['memberType'] = Constants::MEMBER_TYPE_ADMIN;
            $userOwner['isOwner'] = true;

            $members[] = $userOwner;

            unset($workspace->members);

            $workspace->members = $members;
            $workspace['user'] = $userOwner;
        }

        $workspaces = [
            "myWorkspaces"=>$myWorkspaces,
            "guestWorkspaces"=>$guestWorkspaces
        ];

        $r = CustomResponse::ok($workspaces);
        return response()->json($r);
    }*/


    public function store(StoreWorkspaceRequest $request): JsonResponse
    {
        try {

            $this->authorize("create",Workspace::class);

            $workspaceType = WorkspaceType::where('id', $request->workspace_type_id)
                ->first();

            $user = User::where("id", $request->user_id)
                ->where('status', 1)
                ->first();

            if (!$workspaceType || !$user) {
                $r = CustomResponse::badRequest([
                    "data" => "Error en las credenciales"
                ]);
                return response()->json($r, $r->code);
            }

            $workspace = Workspace::create([
                'name' => $request->name,
                'initials' => $request->initials,
                'description' => $request->description,
                'color' => $request->color,
                'user_id' => $user->id,
                'workspace_type_id' => $workspaceType->id,
                'status' => $this->status
            ]);

            return response()->json($workspace);
        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);
        }
        catch (Exception $e) {
            $r = CustomResponse::badRequest("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }


    public function update(StoreWorkspaceRequest $request, int $workspaceId): JsonResponse
    {

        try {
            $this->authorize("update",Workspace::class);

            $workspace = Workspace::where('id',$workspaceId)
                      ->where('status', $this->status)
                      ->first();

            $workspace->name = $request->name;
            $workspace->description = $request->description;
            $workspace->color = $request->color;
            $workspace->initials = $request->initials;

            $workspace->save();

            $r = CustomResponse::ok($workspace);
            return response()->json($r);

        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (Exception $e) {
            $r = CustomResponse::badRequest("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);

          }
    }


    public function destroy(Workspace $workspace)
    {
        //
    }
}
