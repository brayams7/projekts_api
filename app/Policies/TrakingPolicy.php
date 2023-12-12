<?php

namespace App\Policies;

use App\Constants\Constants;
use App\Models\Traking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TrakingPolicy
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
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Traking $traking
     * @return Response|bool
     */
    public function view(User $user, Traking $traking)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Traking $tracking
     * @return Response|bool
     */
    public function update(User $user, Traking $tracking):Response|bool
    {
        return ($user->isGranted(Constants::ROLE_TYPE_MEMBER) && $tracking->user->id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Traking $tracking
     * @return Response|bool
     */
    public function delete(User $user, Traking $tracking):Response|bool
    {
        return ($user->isGranted(Constants::ROLE_TYPE_MEMBER) && $tracking->user->id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Traking $traking
     * @return Response|bool
     */
    public function restore(User $user, Traking $traking)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Traking $traking
     * @return Response|bool
     */
    public function forceDelete(User $user, Traking $traking)
    {
        //
    }
}
