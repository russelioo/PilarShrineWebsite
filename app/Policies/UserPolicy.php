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
        return $actor->isSuperAdmin()
            || $actor->hasPermission('view_users')
            || $actor->hasPermission('parishioners')
            || $actor->hasPermission('staff_management')
            || $actor->hasParishWideAccess()
            || $actor->isCommissionMember();
    }

    /**
     * Determine whether the user can view a specific user record.
     */
    public function view(User $actor, User $target): bool
    {
        if ($actor->isSuperAdmin() || $actor->hasParishWideAccess()) {
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
        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($actor->hasPermission('create_users') || $actor->hasPermission('staff_management')) {
            if ($actor->hasParishWideAccess()) {
                return true;
            }

            return $actor->isCommissionAdmin() && $actor->commission_id !== null;
        }

        return false;
    }

    /**
     * Determine whether the user can update a specific user record.
     */
    public function update(User $actor, User $target): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }

        if (! ($actor->hasPermission('edit_users') || $actor->hasPermission('staff_management'))) {
            return false;
        }

        if ($actor->hasParishWideAccess()) {
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
        if ($actor->id === $target->id) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($actor->hasPermission('delete_users') && $actor->hasParishWideAccess()) {
            return ! $target->isSuperAdmin();
        }

        return false;
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

