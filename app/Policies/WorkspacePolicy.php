<?php

namespace App\Policies;

use App\Constants\Constants;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class WorkspacePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);
    }


    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_ADMIN);
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function sendInvitation(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_ADMIN);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Workspace $workspace
     * @return Response|bool
     */
    public function update(User $user, Workspace $workspace): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_ADMIN);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Workspace $workspace
     * @return Response|bool
     */
    public function delete(User $user, Workspace $workspace)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Workspace $workspace
     * @return Response|bool
     */
    public function restore(User $user, Workspace $workspace)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Workspace $workspace
     * @return Response|bool
     */
    public function forceDelete(User $user, Workspace $workspace)
    {
        //
    }
}
