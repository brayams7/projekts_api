<?php

namespace App\Policies;

use App\Constants\Constants;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use \Illuminate\Auth\Access\Response;
class TagPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param  \App\Models\Tag  $tag
     * @return Response|bool
     */
    public function view(User $user, Tag $tag)
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
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function updateTag(User $user): Response|bool
    {
        return $user->isGranted(Constants::ROLE_TYPE_MEMBER);

    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param  \App\Models\Tag  $tag
     * @return Response|bool
     */
    public function update(User $user, Tag $tag)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param  \App\Models\Tag  $tag
     * @return Response|bool
     */
    public function delete(User $user, Tag $tag)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param  \App\Models\Tag  $tag
     * @return Response|bool
     */
    public function restore(User $user, Tag $tag)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param  \App\Models\Tag  $tag
     * @return Response|bool
     */
    public function forceDelete(User $user, Tag $tag)
    {
        //
    }
}
