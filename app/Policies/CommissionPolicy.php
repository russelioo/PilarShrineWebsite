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
        return $actor->hasParishWideAccess() || $actor->isCommissionMember();
    }

    /**
     * Determine whether the user can view a specific commission.
     */
    public function view(User $actor, Commission $commission): bool
    {
        if ($actor->hasParishWideAccess()) {
            return true;
        }

        return (int) $actor->commission_id === (int) $commission->id;
    }

    /**
     * Determine whether the user can create commissions.
     */
    public function create(User $actor): bool
    {
        return $actor->isSuperAdmin() || $actor->isParishPriest() || $actor->isParishSecretary();
    }

    /**
     * Determine whether the user can update a commission.
     */
    public function update(User $actor, Commission $commission): bool
    {
        if ($actor->isSuperAdmin() || $actor->isParishPriest() || $actor->isParishSecretary()) {
            return true;
        }

        return (int) $commission->head_user_id === (int) $actor->id;
    }

    /**
     * Determine whether the user can delete a commission.
     */
    public function delete(User $actor, Commission $commission): bool
    {
        return $actor->isSuperAdmin();
    }
}

