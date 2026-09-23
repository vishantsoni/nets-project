<?php

namespace App\Policies;

use App\Models\User;
use App\Models\B2BEnquiry;
use Illuminate\Auth\Access\HandlesAuthorization;

class B2BEnquiryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_b2::b::enquiry');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, B2BEnquiry $b2BEnquiry): bool
    {
        return $user->can('view_b2::b::enquiry');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_b2::b::enquiry');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, B2BEnquiry $b2BEnquiry): bool
    {
        return $user->can('update_b2::b::enquiry');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, B2BEnquiry $b2BEnquiry): bool
    {
        return $user->can('delete_b2::b::enquiry');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_b2::b::enquiry');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, B2BEnquiry $b2BEnquiry): bool
    {
        return $user->can('force_delete_b2::b::enquiry');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_b2::b::enquiry');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, B2BEnquiry $b2BEnquiry): bool
    {
        return $user->can('restore_b2::b::enquiry');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_b2::b::enquiry');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, B2BEnquiry $b2BEnquiry): bool
    {
        return $user->can('replicate_b2::b::enquiry');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_b2::b::enquiry');
    }
}
