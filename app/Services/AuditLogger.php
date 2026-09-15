<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Record an immutable audit log entry.
     *
     * @param string $action Action key (e.g., 'user_created', 'role_changed')
     * @param string $description Clear explanation of the event
     * @param Model|null $target The model record affected (User, Commission, etc.)
     * @param int|null $commissionId Associated commission context ID
     * @param array<string, mixed>|null $oldValues Previous state for sensitive attributes
     * @param array<string, mixed>|null $newValues New state for sensitive attributes
     * @param User|null $actor The user who performed the action (defaults to current authenticated user)
     * @param string|null $category Category grouping (default: 'user_management')
     */
    public static function log(
        string $action,
        string $description,
        ?Model $target = null,
        ?int $commissionId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null,
        ?string $category = 'user_management'
    ): AuditLog {
        $actorUser = $actor ?: Auth::user();

        // Target details
        $targetType = null;
        $targetId = null;
        $targetName = null;

        if ($target) {
            $targetType = class_basename($target);
            $targetId = $target->getKey();
            if (isset($target->name)) {
                $targetName = (string) $target->name;
            } elseif (isset($target->title)) {
                $targetName = (string) $target->title;
            } elseif ($target instanceof User) {
                $targetName = $target->displayName;
            }
        }

        // Commission context
        $commId = $commissionId;
        $commName = null;

        if (! $commId && $target instanceof User && $target->commission_id) {
            $commId = $target->commission_id;
        }

        if (! $commId && $actorUser && $actorUser->commission_id) {
            $commId = $actorUser->commission_id;
        }

        if ($commId) {
            $commission = Commission::find($commId);
            $commName = $commission?->name;
        }

        return AuditLog::create([
            'user_id' => $actorUser?->id,
            'user_name' => $actorUser?->name ?: ($actorUser ? 'User #' . $actorUser->id : 'System / Guest'),
            'user_email' => $actorUser?->email,
            'action' => $action,
            'action_category' => $category,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_name' => $targetName,
            'commission_id' => $commId,
            'commission_name' => $commName,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent() ? substr(Request::userAgent(), 0, 500) : null,
        ]);
    }
}

