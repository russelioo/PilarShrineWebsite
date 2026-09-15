<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any user records.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->hasParishWideAccess() || $actor->isCommissionMember();
    }

    /**
     * Determine whether the user can view a specific user record.
     */
    public function view(User $actor, User $target): bool
    {
        if ($actor->hasParishWideAccess()) {
            return true;
        }

        // Commission-level user cannot view higher tier accounts
        if ($target->hasParishWideAccess()) {
            return false;
        }

        // Must belong to the exact same commission
        return $actor->commission_id !== null && $actor->commission_id === $target->commission_id;
    }

    /**
     * Determine whether the user can create new user/staff records.
     */
    public function create(User $actor): bool
    {
        if ($actor->hasParishWideAccess()) {
            return true;
        }

        return $actor->isCommissionAdmin() && $actor->commission_id !== null;
    }

    /**
     * Determine whether the user can update a specific user record.
     */
    public function update(User $actor, User $target): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($actor->isParishPriest() || $actor->isParochialVicar() || $actor->isParishSecretary()) {
            return ! $target->isSuperAdmin();
        }

        if ($actor->isCommissionAdmin()) {
            // Cannot modify higher tiers or users in other commissions
            if ($target->hasParishWideAccess()) {
                return false;
            }

            return $actor->commission_id !== null && $actor->commission_id === $target->commission_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete a specific user record.
     */
    public function delete(User $actor, User $target): bool
    {
        // Only Super Admin can delete, and cannot delete own account
        return $actor->isSuperAdmin() && $actor->id !== $target->id;
    }

    /**
     * Determine whether the user can assign a user to a specific commission.
     */
    public function assignCommission(User $actor, ?int $commissionId): bool
    {
        if ($actor->hasParishWideAccess()) {
            return true;
        }

        // Commission admin can only assign to their own commission
        return $actor->isCommissionAdmin() && $actor->commission_id === $commissionId;
    }
}

