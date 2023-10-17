<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use App\Http\Requests\AddMemberToWorkspaceRequest;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Mail\InviteMemberToWorkspace;
use App\Models\CustomResponse;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceType;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Claims\Collection;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\Payload;
use Tymon\JWTAuth\Validators\PayloadValidator;

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
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }

    }


    public function sendInvitationEmailToWorkspace(AddMemberToWorkspaceRequest $request,string $workspaceId):JsonResponse{
        try {

            $this->authorize("sendInvitation",Workspace::class);

            $workspace = Workspace::where('id',$workspaceId)
                ->where('status', $this->status)
                ->first();

            $username = $request->username;
            $email = $request->email;

            $user = User::where('status', $this->status)
                ->where(function($query) use ($username, $email){
                    $query->where('username', $username)
                        ->orWhere('email', $email);
                })->first();

            if(!$workspace){
                $r = CustomResponse::badRequest("Error en los datos proporcionados");
                return response()->json($r, $r->code);
            }

            if($user){

                $member = self::validateIsMemberInWorkspace($user,$workspace);

                if($member){

                    $r = CustomResponse::badRequest("El usuario ya es miembro");

                }else{

                    $expTimestamp = now()->addHours(24)->timestamp;

                    $claims = JWTFactory::customClaims([
                        'iss' => 'projekts',
                        'iat' => now()->timestamp,
                        'sub'=>$user->id,
                        'exp'=>$expTimestamp,
                        'email'=>$email,
                        'workspaceId'=>$workspace->id
                    ])->make();

                    $token  = JWTAuth::encode($claims);

                    $url = Constants::BASE_APP_FE.'inviteTeam?email='.$email.'&token='.$token;

                    Mail::to($email)->send(new InviteMemberToWorkspace($url, $workspace->name));

                    $r = CustomResponse::ok($url);
                }

                return response()->json($r, $r->code);

            }else{

                $expTimestamp = now()->addHours(24)->timestamp;

                $claims = JWTFactory::customClaims([
                    'iss' => 'projekts',
                    'iat' => now()->timestamp,
                    'sub'=>'',
                    'exp'=>$expTimestamp,
                    'email'=>$email,
                    'workspaceId'=>$workspace->id
                ])->make();

                $token  = JWTAuth::encode($claims);

                $url = Constants::BASE_APP_FE.'inviteTeam?email='.$email.'&token='.$token;

                Mail::to($email)->send(new InviteMemberToWorkspace($url, $workspace->name));

                $r = CustomResponse::ok($url);
                return response()->json($r);
            }

        }catch (AuthorizationException $e){
            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);
        }catch (Exception $e){
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }

    public function acceptInvitationToWorkspace(AddMemberToWorkspaceRequest $request):JsonResponse{
        try {

            $token = $request->token;
            $email = $request->email;

            $parceToken = JWTAuth::setToken($token)->parseToken();

            if ($parceToken->check()) {

                $claims = $parceToken->getPayload();
                $userId = $claims->get('sub');
                $workspaceId = $claims->get('workspaceId');

                $user = User::where('id', $userId)
                    ->where('status', $this->status)
                    ->first();

                $workspace = Workspace::where('id',$workspaceId)
                    ->where('status', $this->status)
                    ->first();

                if($user){

                    if($workspace && !self::validateIsMemberInWorkspace($user,$workspace)){

                        $workspace->members()->attach($user->id);
                    }

                    $data = [
                        'email'=>$email,
                        'token'=>$token,
                        'is_add_user'=>true,
                        'is_register_user'=>true,
                        'workspace_id'=>$workspaceId
                    ];

                }else{
                    $data = [
                        'email'=>$email,
                        'token'=>$token,
                        'is_add_user'=>false,
                        'is_register_user'=>false,
                        'workspace_id'=>$workspaceId
                    ];
                }

                $r = CustomResponse::ok($data);
                return response()->json($r, $r->code);

            } else {
                // El token no es válido
                $r = CustomResponse::unAuthorized("Token no valido");
            }

            return response()->json($r, $r->code);

        }catch (TokenExpiredException $e) {
            // El token ha expirado
            $r = CustomResponse::unAuthorized("Token expirado");
            return response()->json($r, $r->code);
        } catch (TokenInvalidException $e) {
            // El token no es válido
            $r = CustomResponse::unAuthorized("Token no valido");
            return response()->json($r, $r->code);
        } catch (JWTException $e) {
            // Ocurrió un error al procesar el token
            $r = CustomResponse::unAuthorized("No se pudo procesar el token");
            return response()->json($r, $r->code);
        }catch (Exception $e){
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }


    /*public function addMemberToWorkspace(Workspace $workspace, User $user){
        try {
            $member = self::validateIsMemberInWorkspace($user,$workspace);
            if($member){

                $r = CustomResponse::badRequest("El usuario ya es miembro");

            }else{
                $workspace->members()->updateExistingPivot($user->id);

                $r = CustomResponse::ok("Usuario añadido correctamente");
            }
        }catch (Exception $exception){
            return false;
        }
    }*/
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


    public function update(StoreWorkspaceRequest $request, $workspaceId): JsonResponse
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

}
