<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        //User has global permission to create publications
        if ($user->can(Permission::CREATE_PUBLICATION)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view publications.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function view(User $user)
    {
        //User has global permission to view all publications
        if ($user->can(Permission::VIEW_ALL_PUBLICATIONS)) {
            return true;
        }

        return false;
    }
}
