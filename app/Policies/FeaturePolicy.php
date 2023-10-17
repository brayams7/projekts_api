<?php

namespace App\Policies;

use App\Constants\Constants;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
class FeaturePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Feature $feature
     * @return Response|bool
     */
    public function view(User $user, Feature $feature)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_ADMIN);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function createComment(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function addAttachment(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function deleteAttachment(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function changeOrder(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function getDetail(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function assignUserToFeature(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_ADMIN);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function deleteUserToFeature(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_ADMIN);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function changeVisibilityFromUserToAFeaturePolicy(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function updateFeaturePolicy(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Feature $feature
     * @return Response|bool
     */
    public function delete(User $user, Feature $feature)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Feature $feature
     * @return Response|bool
     */
    public function restore(User $user, Feature $feature)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Feature $feature
     * @return Response|bool
     */
    public function forceDelete(User $user, Feature $feature)
    {
        //
    }
}
