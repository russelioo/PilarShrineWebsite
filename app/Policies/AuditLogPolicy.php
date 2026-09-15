<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view audit logs.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->hasParishWideAccess() || $actor->isCommissionAdmin();
    }

    /**
     * Determine whether the user can view a specific audit log.
     */
    public function view(User $actor, AuditLog $log): bool
    {
        if ($actor->hasParishWideAccess()) {
            return true;
        }

        if ($actor->isCommissionAdmin() && $actor->commission_id !== null) {
            return (int) $actor->commission_id === (int) $log->commission_id;
        }

        return false;
    }

    /**
     * Audit logs are immutable and cannot be modified.
     */
    public function update(User $actor, AuditLog $log): bool
    {
        return false;
    }

    /**
     * Audit logs are immutable and cannot be deleted.
     */
    public function delete(User $actor, AuditLog $log): bool
    {
        return false;
    }
}

