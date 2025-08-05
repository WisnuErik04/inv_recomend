<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ExitItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExitItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_exit::item');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExitItem $exitItem): bool
    {
        return $user->can('view_exit::item');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_exit::item');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExitItem $exitItem): bool
    {
        return $user->can('update_exit::item');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExitItem $exitItem): bool
    {
        return $user->can('delete_exit::item');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_exit::item');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ExitItem $exitItem): bool
    {
        return $user->can('force_delete_exit::item');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_exit::item');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ExitItem $exitItem): bool
    {
        return $user->can('restore_exit::item');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_exit::item');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, ExitItem $exitItem): bool
    {
        return $user->can('replicate_exit::item');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_exit::item');
    }
}
