<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AppSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppSettingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_electronic::billing::config');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AppSetting $appSetting): bool
    {
        return $user->can('view_electronic::billing::config');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_electronic::billing::config');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AppSetting $appSetting): bool
    {
        return $user->can('update_electronic::billing::config');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AppSetting $appSetting): bool
    {
        return $user->can('delete_electronic::billing::config');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_electronic::billing::config');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, AppSetting $appSetting): bool
    {
        return $user->can('force_delete_electronic::billing::config');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_electronic::billing::config');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, AppSetting $appSetting): bool
    {
        return $user->can('restore_electronic::billing::config');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_electronic::billing::config');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, AppSetting $appSetting): bool
    {
        return $user->can('replicate_electronic::billing::config');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_electronic::billing::config');
    }
}
