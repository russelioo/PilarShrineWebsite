<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;

class CommissionPolicy
{
    /**
     * Determine whether the user can view commissions.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->isSuperAdmin() || $actor->hasPermission('view_commissions') || $actor->hasPermission('commissions') || $actor->hasParishWideAccess() || $actor->isCommissionMember();
    }

    /**
     * Determine whether the user can view a specific commission.
     */
    public function view(User $actor, Commission $commission): bool
    {
        if ($actor->isSuperAdmin() || $actor->hasParishWideAccess() || $actor->hasPermission('view_commissions') || $actor->hasPermission('commissions')) {
            return true;
        }

        return (int) $actor->commission_id === (int) $commission->id;
    }

    /**
     * Determine whether the user can create commissions.
     */
    public function create(User $actor): bool
    {
        return $actor->isSuperAdmin() || $actor->hasPermission('create_commissions') || $actor->hasPermission('commissions');
    }

    /**
     * Determine whether the user can update a commission.
     */
    public function update(User $actor, Commission $commission): bool
    {
        if ($actor->isSuperAdmin() || $actor->hasPermission('edit_commissions') || $actor->hasPermission('commissions')) {
            return true;
        }

        return (int) $commission->head_user_id === (int) $actor->id;
    }

    /**
     * Determine whether the user can delete a commission.
     */
    public function delete(User $actor, Commission $commission): bool
    {
        return $actor->isSuperAdmin() || $actor->hasPermission('delete_commissions');
    }
}

