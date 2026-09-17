<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;

class CommissionPolicy
{
    /**
     * Determine whether the user can view commissions listing.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->isSuperAdmin()
            || $actor->hasParishWideAccess()
            || $actor->hasPermission('view_commissions')
            || $actor->hasPermission('commissions')
            || $actor->isCommissionMember();
    }

    /**
     * Determine whether the user can view a specific commission.
     * Enforces strict data isolation: non-parish-wide users can only view their own commission.
     */
    public function view(User $actor, Commission $commission): bool
    {
        if ($actor->isSuperAdmin() || $actor->hasParishWideAccess()) {
            return true;
        }

        return $actor->canAccessCommission($commission->id);
    }

    /**
     * Determine whether the user can create commissions.
     */
    public function create(User $actor): bool
    {
        return $actor->isSuperAdmin()
            || $actor->hasPermission('create_commissions')
            || $actor->hasPermission('commissions.create');
    }

    /**
     * Determine whether the user can update a commission.
     */
    public function update(User $actor, Commission $commission): bool
    {
        if ($actor->isSuperAdmin() || $actor->hasParishWideAccess()) {
            return true;
        }

        if ($actor->canAccessCommission($commission->id)) {
            return (int) $commission->head_user_id === (int) $actor->id
                || $actor->isCommissionAdmin()
                || $actor->hasPermission('edit_commissions');
        }

        return false;
    }

    /**
     * Determine whether the user can activate or deactivate a commission.
     */
    public function toggle(User $actor, Commission $commission): bool
    {
        return $actor->isSuperAdmin()
            || $actor->hasPermission('activate_commissions')
            || $actor->hasPermission('deactivate_commissions');
    }

    /**
     * Determine whether the user can delete a commission.
     */
    public function delete(User $actor, Commission $commission): bool
    {
        return $actor->isSuperAdmin() || $actor->hasPermission('delete_commissions');
    }

    /**
     * Determine whether the user can manage members of this commission.
     */
    public function manageMembers(User $actor, Commission $commission): bool
    {
        if ($actor->isSuperAdmin() || $actor->hasParishWideAccess()) {
            return true;
        }

        if ($actor->canAccessCommission($commission->id)) {
            return (int) $commission->head_user_id === (int) $actor->id
                || $actor->isCommissionAdmin()
                || $actor->hasPermission('add_commission_members')
                || $actor->hasPermission('remove_commission_members');
        }

        return false;
    }

    /**
     * Determine whether the user can manage officers of this commission.
     */
    public function manageOfficers(User $actor, Commission $commission): bool
    {
        if ($actor->isSuperAdmin() || $actor->hasParishWideAccess()) {
            return true;
        }

        if ($actor->canAccessCommission($commission->id)) {
            return (int) $commission->head_user_id === (int) $actor->id
                || $actor->isCommissionAdmin();
        }

        return false;
    }

    /**
     * Determine whether the user can manage projects of this commission.
     */
    public function manageProjects(User $actor, Commission $commission): bool
    {
        if ($actor->isSuperAdmin() || $actor->hasParishWideAccess()) {
            return true;
        }

        if ($actor->canAccessCommission($commission->id)) {
            return (int) $commission->head_user_id === (int) $actor->id
                || $actor->isCommissionAdmin()
                || $actor->hasPermission('create_projects')
                || $actor->hasPermission('edit_projects');
        }

        return false;
    }

    /**
     * Determine whether the user can manage documents of this commission.
     */
    public function manageDocuments(User $actor, Commission $commission): bool
    {
        if ($actor->isSuperAdmin() || $actor->hasParishWideAccess()) {
            return true;
        }

        if ($actor->canAccessCommission($commission->id)) {
            return (int) $commission->head_user_id === (int) $actor->id
                || $actor->isCommissionAdmin()
                || $actor->hasPermission('upload_documents')
                || $actor->hasPermission('manage_documents');
        }

        return false;
    }

    /**
     * Determine whether the user can create accounts for this commission.
     */
    public function createAccount(User $actor, Commission $commission): bool
    {
        return $actor->isSuperAdmin()
            || ($actor->hasParishWideAccess() && $actor->hasPermission('create_users'));
    }
}
