<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'first_name', 'last_name', 'date_of_birth', 'country', 'region', 'province', 'municipality_city', 'barangay', 'email', 'password_hash', 'role', 'organization', 'position', 'responsibilities', 'permissions', 'commission_id', 'phone', 'is_verified', 'email_verified_at', 'google_id', 'avatar'])]
#[Hidden(['password_hash', 'remember_token', 'reset_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password_hash' => 'hashed',
            'last_login' => 'datetime',
            'last_active_at' => 'datetime',
            'is_verified' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function isProfileComplete(): bool
    {
        return !empty($this->first_name)
            && !empty($this->last_name)
            && !empty($this->date_of_birth)
            && !empty($this->phone)
            && !empty($this->barangay);
    }

    public function massIntentions(): HasMany
    {
        return $this->hasMany(MassIntention::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function ministryMemberships(): HasMany
    {
        return $this->hasMany(MinistryMembership::class);
    }

    public function ministries(): BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'ministry_memberships')
            ->withPivot('status', 'joined_at', 'reviewed_at')
            ->withTimestamps();
    }

    public function activeMinistries(): BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'ministry_memberships')
            ->wherePivot('status', 'approved')
            ->withPivot('joined_at', 'reviewed_at')
            ->withTimestamps();
    }

    public function coordinatedMinistries(): HasMany
    {
        return $this->hasMany(Ministry::class, 'coordinator_user_id');
    }

    public function getInitialsAttribute(): string
    {
        if (!empty($this->first_name) && !empty($this->last_name)) {
            return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
        }

        $parts = preg_split('/\s+/', trim($this->name ?: ''));
        if (!empty($parts[0])) {
            $second = isset($parts[1]) ? substr($parts[1], 0, 1) : '';
            return strtoupper(substr($parts[0], 0, 1) . $second);
        }

        return 'PA';
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            if ($this->isParishAdministrator()) {
                return '/images/pilar-shrine-logo.png';
            }
            return null;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        if (str_starts_with($this->avatar, '/storage/')) {
            return $this->avatar;
        }

        if (str_starts_with($this->avatar, 'storage/')) {
            return '/' . $this->avatar;
        }

        return '/storage/' . ltrim($this->avatar, '/');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name
            ?: trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''))
            ?: 'Parishioner';
    }

    public function getAuthProviderAttribute(): string
    {
        return !empty($this->google_id) ? 'google' : 'password';
    }

    public function commission(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Commission::class);
    }

    public function commissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Commission::class, 'commission_memberships')
            ->withPivot('id', 'position', 'is_officer', 'role', 'status', 'joined_at', 'notes')
            ->withTimestamps();
    }

    public function ppcMemberships(): HasMany
    {
        return $this->hasMany(PpcMember::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function targetedAuditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'target_id')
            ->where('target_type', 'User');
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin'], true);
    }

    public function isParishPriest(): bool
    {
        return $this->role === 'parish_priest';
    }

    public function isParochialVicar(): bool
    {
        return $this->role === 'parochial_vicar';
    }

    public function isParishSecretary(): bool
    {
        return $this->role === 'parish_secretary';
    }

    public function hasParishWideAccess(): bool
    {
        if (in_array($this->role, ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary'], true)) {
            return true;
        }

        if (is_array($this->permissions) && in_array('all_commissions', $this->permissions, true)) {
            return true;
        }

        return false;
    }

    public function hasParishWideCommissionOversight(): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        return is_array($this->permissions) && in_array('all_commissions', $this->permissions, true);
    }

    public function canManagePermissions(): bool
    {
        return $this->role === 'super_admin'
            || $this->hasPermission('modify_permissions')
            || $this->hasPermission('assign_permissions');
    }

    /**
     * Complete granular permissions catalog grouped by module (13 modules).
     */
    public static function getAllAvailablePermissions(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'permissions' => [
                    'view_dashboard' => 'View Dashboard',
                    'view_analytics' => 'View Website Analytics',
                ],
            ],
            'users' => [
                'label' => 'Users',
                'permissions' => [
                    'view_users'        => 'View Users',
                    'create_users'      => 'Create Users',
                    'edit_users'        => 'Edit Users',
                    'delete_users'      => 'Delete Users',
                    'activate_users'    => 'Activate Users',
                    'deactivate_users'  => 'Deactivate Users',
                    'view_user_details' => 'View User Details',
                ],
            ],
            'roles' => [
                'label' => 'Roles',
                'permissions' => [
                    'view_roles'   => 'View Roles',
                    'create_roles' => 'Create Roles',
                    'edit_roles'   => 'Edit Roles',
                    'delete_roles' => 'Delete Roles',
                ],
            ],
            'permissions' => [
                'label' => 'Permissions',
                'permissions' => [
                    'view_permissions'   => 'View Permissions',
                    'assign_permissions' => 'Assign Permissions',
                    'modify_permissions' => 'Modify Permissions',
                    'revoke_permissions' => 'Revoke Permissions',
                ],
            ],
            'commissions' => [
                'label' => 'Commissions',
                'permissions' => [
                    'view_commissions'              => 'View Commissions',
                    'create_commissions'            => 'Create Commission',
                    'edit_commissions'              => 'Edit Commission',
                    'delete_commissions'            => 'Delete Commission',
                    'activate_commissions'          => 'Activate Commission',
                    'deactivate_commissions'        => 'Deactivate Commission',
                    'view_commission_members'       => 'View Commission Members',
                    'add_commission_members'        => 'Add Members',
                    'remove_commission_members'     => 'Remove Members',
                    'assign_commission_coordinators' => 'Assign Coordinator',
                    'manage_commission_info'        => 'Manage Commission Information',
                    'all_commissions'               => 'Parish-wide Commission Oversight',
                ],
            ],
            'ministries' => [
                'label' => 'Ministries',
                'permissions' => [
                    'view_ministries'              => 'View Ministries',
                    'create_ministries'            => 'Create Ministry',
                    'edit_ministries'              => 'Edit Ministry',
                    'delete_ministries'            => 'Delete Ministry',
                    'view_ministry_members'        => 'View Ministry Members',
                    'add_ministry_members'         => 'Add Members',
                    'remove_ministry_members'      => 'Remove Members',
                    'assign_ministry_coordinators' => 'Assign Coordinator',
                    'all_ministries'               => 'Parish-wide Ministry Oversight',
                ],
            ],
            'messages' => [
                'label' => 'Messages',
                'permissions' => [
                    'view_messages'    => 'View Messages',
                    'send_messages'    => 'Send Messages',
                    'reply_messages'   => 'Reply to Messages',
                    'archive_messages' => 'Archive Messages',
                    'delete_messages'  => 'Delete Messages',
                ],
            ],
            'announcements' => [
                'label' => 'Announcements',
                'permissions' => [
                    'view_announcements'    => 'View Announcements',
                    'create_announcements'  => 'Create Announcements',
                    'edit_announcements'    => 'Edit Announcements',
                    'delete_announcements'  => 'Delete Announcements',
                    'publish_announcements' => 'Publish Announcements',
                ],
            ],
            'documents' => [
                'label' => 'Documents',
                'permissions' => [
                    'view_documents'     => 'View Documents',
                    'upload_documents'   => 'Upload Documents',
                    'edit_documents'     => 'Edit Documents',
                    'delete_documents'   => 'Delete Documents',
                    'download_documents' => 'Download Documents',
                ],
            ],
            'requests' => [
                'label' => 'Requests',
                'permissions' => [
                    'view_requests'    => 'View Requests',
                    'create_requests'  => 'Create Requests',
                    'edit_requests'    => 'Edit Requests',
                    'approve_requests' => 'Approve Requests',
                    'reject_requests'  => 'Reject Requests',
                    'close_requests'   => 'Close Requests',
                ],
            ],
            'reports' => [
                'label' => 'Reports',
                'permissions' => [
                    'view_reports'     => 'View Reports',
                    'generate_reports' => 'Generate Reports',
                    'export_reports'   => 'Export Reports',
                ],
            ],
            'audit_logs' => [
                'label' => 'Audit Logs',
                'permissions' => [
                    'view_audit_logs'   => 'View Audit Logs',
                    'export_audit_logs' => 'Export Audit Logs',
                ],
            ],
            'settings' => [
                'label' => 'Settings',
                'permissions' => [
                    'view_settings' => 'View Settings',
                    'edit_settings' => 'Edit Settings',
                ],
            ],
            'liturgy' => [
                'label' => 'Liturgy & Sacraments',
                'permissions' => [
                    'mass_schedules'  => 'Mass & Confession Schedule',
                    'mass_intentions' => 'Mass Intentions Management',
                    'donations'       => 'Donations Verification & Records',
                ],
            ],
        ];
    }

    /**
     * Get all permission keys from the catalog.
     */
    public static function getAllPermissionKeys(): array
    {
        $keys = [];
        foreach (static::getAllAvailablePermissions() as $group) {
            foreach (array_keys($group['permissions']) as $pKey) {
                $keys[] = $pKey;
            }
        }
        return $keys;
    }

    /**
     * Default baseline permissions for each role. Can be customized per user.
     */
    public static function getDefaultPermissionsForRole(?string $role): array
    {
        $role = $role ?: 'user';
        $all = static::getAllPermissionKeys();

        return match ($role) {
            'super_admin', 'admin' => $all,

            'parish_priest' => [
                'view_dashboard',
                'view_users', 'view_user_details', 'view_roles',
                'view_commissions', 'view_commission_members',
                'view_ministries', 'view_ministry_members',
                'view_messages', 'send_messages', 'reply_messages', 'archive_messages',
                'view_announcements', 'create_announcements', 'publish_announcements',
                'view_documents', 'upload_documents',
                'view_requests', 'approve_requests', 'reject_requests',
                'view_reports', 'generate_reports', 'export_reports',
                'view_audit_logs',
                'mass_schedules', 'mass_intentions', 'donations',
            ],

            'parochial_vicar' => [
                'view_dashboard',
                'view_users', 'view_user_details',
                'view_commissions', 'view_commission_members',
                'view_ministries', 'view_ministry_members',
                'view_messages', 'send_messages', 'reply_messages',
                'view_announcements',
                'view_documents',
                'view_requests', 'approve_requests', 'reject_requests',
                'mass_schedules', 'mass_intentions',
            ],

            'parish_secretary' => [
                'view_dashboard',
                'view_users', 'create_users', 'edit_users', 'view_user_details',
                'view_commissions', 'view_commission_members',
                'view_ministries', 'view_ministry_members',
                'view_messages', 'send_messages', 'reply_messages', 'archive_messages',
                'view_announcements', 'create_announcements', 'edit_announcements', 'publish_announcements',
                'view_documents', 'upload_documents', 'download_documents',
                'view_requests', 'create_requests', 'edit_requests', 'approve_requests', 'reject_requests', 'close_requests',
                'view_reports',
                'view_audit_logs',
                'mass_schedules', 'mass_intentions', 'donations',
            ],

            'commission_admin' => [
                'view_dashboard',
                'view_users', 'create_users', 'edit_users', 'view_user_details',
                'view_commissions', 'view_commission_members', 'add_commission_members', 'remove_commission_members', 'manage_commission_info',
                'view_messages', 'send_messages', 'reply_messages',
                'view_announcements', 'create_announcements', 'publish_announcements',
                'view_documents', 'upload_documents',
                'view_requests', 'approve_requests',
                'view_audit_logs',
                'mass_schedules',
            ],

            'commission_member' => [
                'view_dashboard',
                'view_commissions', 'view_commission_members',
                'view_messages', 'send_messages', 'reply_messages',
                'view_announcements',
                'view_documents', 'download_documents',
            ],

            'staff' => [
                'view_dashboard',
                'view_users', 'view_user_details',
                'view_commissions', 'view_commission_members',
                'view_messages', 'send_messages', 'reply_messages',
                'view_announcements', 'create_announcements',
                'view_documents',
                'view_requests',
                'mass_schedules', 'mass_intentions',
            ],

            'user' => [
                'view_dashboard',
                'view_messages', 'send_messages',
                'view_announcements',
                'view_documents',
                'view_requests', 'create_requests',
            ],

            default => ['view_dashboard', 'view_messages'],
        };
    }

    /**
     * Symmetrically resolve all valid alias representations of a permission
     * (dot notation, underscore notation, and module-level names).
     *
     * @return array<string>
     */
    public static function resolvePermissionAliases(string $permission): array
    {
        $aliases = [$permission];

        // 1. Bidirectional Dot <-> Underscore conversion
        if (str_contains($permission, '.')) {
            [$mod, $act] = explode('.', $permission, 2);
            $aliases[] = "{$act}_{$mod}";
            if ($act === 'view') {
                $aliases[] = $mod;
                $aliases[] = "view_{$mod}";
            }
        } elseif (preg_match('/^(view|create|edit|delete|publish|archive|send|upload|download|assign|modify|revoke|generate|export)_(.+)$/', $permission, $m)) {
            $act = $m[1];
            $mod = $m[2];
            $aliases[] = "{$mod}.{$act}";
            if ($act === 'view') {
                $aliases[] = $mod;
            }
        }

        // 2. High-level module and action semantic equivalence matrix
        $matrix = [
            'messages'           => ['view_messages', 'messages.view'],
            'view_messages'      => ['messages', 'messages.view'],
            'messages.view'      => ['messages', 'view_messages'],
            'messages.create'    => ['send_messages', 'messages.send'],
            'messages.send'      => ['send_messages', 'messages.create'],
            'send_messages'      => ['messages.send', 'messages.create'],

            'parishioners'       => ['view_users', 'users.view', 'users', 'manage_users'],
            'view_users'         => ['parishioners', 'users.view', 'users', 'manage_users'],
            'users.view'         => ['parishioners', 'view_users', 'users', 'manage_users'],
            'users'              => ['view_users', 'users.view', 'parishioners', 'manage_users'],
            'create_users'       => ['users.create', 'manage_users'],
            'users.create'       => ['create_users', 'manage_users'],
            'edit_users'         => ['users.edit', 'manage_users'],
            'users.edit'         => ['edit_users', 'manage_users'],
            'delete_users'       => ['users.delete', 'manage_users'],
            'users.delete'       => ['delete_users', 'manage_users'],
            'activate_users'     => ['manage_users'],
            'deactivate_users'   => ['manage_users'],
            'view_user_details'  => ['users.view', 'view_users', 'manage_users'],

            'staff_management'   => ['view_users', 'create_users', 'edit_users', 'users.view', 'manage_users'],

            'roles'              => ['view_roles', 'roles.view', 'manage_users'],
            'view_roles'         => ['roles', 'roles.view', 'manage_users'],
            'roles.view'         => ['roles', 'view_roles', 'manage_users'],
            'create_roles'       => ['roles.create', 'manage_users'],
            'roles.create'       => ['create_roles', 'manage_users'],
            'edit_roles'         => ['roles.edit', 'manage_users'],
            'roles.edit'         => ['edit_roles', 'manage_users'],
            'delete_roles'       => ['roles.delete', 'manage_users'],
            'roles.delete'       => ['delete_roles', 'manage_users'],

            'permissions'        => ['view_permissions', 'permissions.view', 'modify_permissions', 'assign_permissions', 'manage_users'],
            'view_permissions'   => ['permissions', 'permissions.view', 'modify_permissions', 'assign_permissions', 'manage_users'],
            'permissions.view'   => ['permissions', 'view_permissions', 'modify_permissions', 'assign_permissions', 'manage_users'],
            'modify_permissions' => ['assign_permissions', 'permissions.edit', 'permissions.modify', 'manage_users'],
            'assign_permissions' => ['modify_permissions', 'permissions.edit', 'permissions.assign', 'manage_users'],
            'revoke_permissions' => ['modify_permissions', 'assign_permissions', 'manage_users'],

            'audit_logs'         => ['view_audit_logs', 'audit_logs.view'],
            'view_audit_logs'    => ['audit_logs', 'audit_logs.view'],
            'audit_logs.view'    => ['audit_logs', 'view_audit_logs'],

            'manage_ministries'            => ['view_ministries', 'ministries.view', 'ministries', 'all_ministries'],
            'view_ministries'              => ['manage_ministries', 'ministries.view', 'ministries', 'all_ministries'],
            'ministries.view'              => ['manage_ministries', 'view_ministries', 'ministries', 'all_ministries'],
            'ministries'                   => ['view_ministries', 'ministries.view', 'manage_ministries', 'all_ministries'],
            'create_ministries'            => ['ministries.create', 'all_ministries'],
            'edit_ministries'              => ['ministries.edit', 'all_ministries'],
            'delete_ministries'            => ['ministries.delete', 'all_ministries'],
            'view_ministry_members'        => ['all_ministries'],
            'add_ministry_members'         => ['all_ministries'],
            'remove_ministry_members'      => ['all_ministries'],
            'assign_ministry_coordinators' => ['all_ministries'],

            'commissions'                    => ['view_commissions', 'commissions.view', 'all_commissions'],
            'view_commissions'               => ['commissions', 'commissions.view', 'all_commissions'],
            'commissions.view'               => ['commissions', 'view_commissions', 'all_commissions'],
            'create_commissions'             => ['commissions.create', 'all_commissions'],
            'edit_commissions'               => ['commissions.edit', 'all_commissions'],
            'delete_commissions'             => ['commissions.delete', 'all_commissions'],
            'activate_commissions'           => ['all_commissions'],
            'deactivate_commissions'         => ['all_commissions'],
            'view_commission_members'        => ['all_commissions'],
            'add_commission_members'         => ['all_commissions'],
            'remove_commission_members'      => ['all_commissions'],
            'assign_commission_coordinators' => ['all_commissions'],
            'manage_commission_info'         => ['all_commissions'],

            'ministry_requests'  => ['view_requests', 'requests.view', 'requests', 'approve_requests', 'manage_records'],
            'view_requests'      => ['ministry_requests', 'requests.view', 'requests', 'manage_records'],
            'requests.view'      => ['ministry_requests', 'view_requests', 'requests', 'manage_records'],
            'approve_requests'   => ['requests.approve', 'ministry_requests', 'manage_records'],
            'requests.approve'   => ['approve_requests', 'ministry_requests', 'manage_records'],
            'reject_requests'    => ['requests.reject', 'ministry_requests', 'manage_records'],
            'requests.reject'    => ['reject_requests', 'ministry_requests', 'manage_records'],
            'create_requests'    => ['requests.create', 'manage_records'],
            'edit_requests'      => ['requests.edit', 'manage_records'],
            'close_requests'     => ['requests.close', 'manage_records'],
            'requests'           => ['view_requests', 'requests.view', 'ministry_requests', 'manage_records'],

            'documents'          => ['view_documents', 'documents.view', 'manage_records'],
            'view_documents'     => ['documents', 'documents.view', 'manage_records'],
            'documents.view'     => ['documents', 'view_documents', 'manage_records'],
            'upload_documents'   => ['documents.upload', 'manage_records'],
            'edit_documents'     => ['documents.edit', 'manage_records'],
            'delete_documents'   => ['documents.delete', 'manage_records'],
            'download_documents' => ['documents.download', 'manage_records'],

            'mass_schedules'     => ['manage_schedules', 'mass_schedules.view'],
            'mass_intentions'    => ['mass_intentions.view', 'mass_schedules', 'manage_schedules'],

            'announcements'         => ['view_announcements', 'announcements.view', 'manage_records'],
            'view_announcements'    => ['announcements', 'announcements.view', 'manage_records'],
            'announcements.view'    => ['announcements', 'view_announcements', 'manage_records'],
            'create_announcements'  => ['announcements.create', 'manage_records'],
            'edit_announcements'    => ['announcements.edit', 'manage_records'],
            'delete_announcements'  => ['announcements.delete', 'manage_records'],
            'publish_announcements' => ['announcements.publish', 'manage_records'],

            'donations'          => ['donations.view', 'view_reports', 'manage_records'],
            'view_reports'       => ['reports.view', 'reports', 'donations', 'manage_records'],
            'reports'            => ['view_reports', 'reports.view', 'manage_records'],
            'generate_reports'   => ['reports.generate', 'manage_records'],
            'export_reports'     => ['reports.export', 'manage_records'],

            'dashboard'          => ['view_dashboard', 'dashboard.view'],
            'view_dashboard'     => ['dashboard', 'dashboard.view'],
            'dashboard.view'     => ['dashboard', 'view_dashboard'],

            'settings'           => ['view_settings', 'settings.view'],
            'view_settings'      => ['settings', 'settings.view'],
            'settings.view'      => ['settings', 'view_settings'],
        ];

        if (isset($matrix[$permission])) {
            foreach ($matrix[$permission] as $extra) {
                $aliases[] = $extra;
            }
        }

        return array_values(array_unique($aliases));
    }

    public function hasPermission(string $permission): bool
    {
        // Super Admin retains centralized full bypass
        if ($this->role === 'super_admin') {
            return true;
        }

        $aliases = static::resolvePermissionAliases($permission);
        $perms = is_array($this->permissions) ? $this->permissions : null;

        // If explicit custom permissions array is configured on the user record
        if ($perms !== null) {
            if (in_array('*', $perms, true)) {
                return true;
            }
            foreach ($aliases as $alias) {
                if (in_array($alias, $perms, true)) {
                    return true;
                }
            }
            return false;
        }

        // Fall back to default role baseline permissions
        $defaults = static::getDefaultPermissionsForRole($this->role ?? 'user');
        if (in_array('*', $defaults, true)) {
            return true;
        }
        foreach ($aliases as $alias) {
            if (in_array($alias, $defaults, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the active list of permission keys for this user.
     * Expands legacy tags or falls back to role defaults.
     *
     * @return array<string>
     */
    public function getEffectivePermissions(): array
    {
        if ($this->role === 'super_admin') {
            return static::getAllPermissionKeys();
        }

        if ($this->permissions === null) {
            return static::getDefaultPermissionsForRole($this->role ?? 'user');
        }

        if (in_array('*', $this->permissions, true)) {
            return static::getAllPermissionKeys();
        }

        $keys = static::getAllPermissionKeys();
        $effective = [];
        foreach ($keys as $key) {
            if ($this->hasPermission($key)) {
                $effective[] = $key;
            }
        }

        return array_values(array_unique($effective));
    }

    /**
     * Get all organizations this user is connected to (Commissions and Ministries).
     */
    public function getAvailableOrganizations(): array
    {
        $orgs = [];
        if ($this->isParishAdministration()) {
            $orgs[] = [
                'type' => 'parish_administration',
                'id' => null,
                'name' => 'Parish Administration',
                'role' => $this->position ?: $this->role_badge_label,
            ];
        }

        foreach ($this->commissions as $comm) {
            $orgs[] = [
                'type' => 'commission',
                'id' => $comm->id,
                'name' => $comm->name,
                'role' => ucfirst($comm->pivot->role ?? 'member'),
            ];
        }

        foreach ($this->ministries as $min) {
            $orgs[] = [
                'type' => 'ministry',
                'id' => $min->id,
                'name' => $min->name,
                'role' => ucfirst($min->pivot->role ?? 'member'),
            ];
        }

        return $orgs;
    }

    /**
     * Get the active organization context for the current session.
     */
    public function getActiveOrganizationContext(): array
    {
        $sessionCtx = session('active_organization_context');
        if (is_array($sessionCtx) && ! empty($sessionCtx['name'])) {
            return $sessionCtx;
        }

        $available = $this->getAvailableOrganizations();
        return ! empty($available) ? $available[0] : [
            'type' => $this->organization ?: 'parishioner',
            'id' => $this->commission_id,
            'name' => $this->organization_label,
            'role' => $this->position ?: $this->role_badge_label,
        ];
    }

    public function isParishAdministration(): bool
    {
        return $this->organization === 'parish_administration'
            || in_array($this->role, ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary'], true);
    }

    public function isCommissionAdmin(): bool
    {
        return $this->role === 'commission_admin' || $this->position === 'Commission Coordinator';
    }

    public function isCommissionMember(): bool
    {
        return in_array($this->role, ['commission_admin', 'commission_member', 'staff'], true)
            || $this->organization === 'commission'
            || $this->commissions()->exists();
    }

    public function canAccessCommission(?int $commissionId): bool
    {
        if (! $commissionId) {
            return false;
        }

        if ($this->hasParishWideAccess()) {
            return true;
        }

        if ((int) $this->commission_id === (int) $commissionId) {
            return true;
        }

        return $this->commissions()->where('commissions.id', $commissionId)->exists();
    }

    public function canAccessMinistry(?int $ministryId): bool
    {
        if (! $ministryId) {
            return false;
        }

        if ($this->hasParishWideAccess()) {
            return true;
        }

        if ($this->coordinatedMinistries()->where('id', $ministryId)->exists()) {
            return true;
        }

        return $this->ministries()->where('ministries.id', $ministryId)->wherePivot('status', 'approved')->exists();
    }

    /**
     * Check if the user is currently online (active within the last 5 minutes).
     */
    public function isOnline(): bool
    {
        return $this->last_active_at && $this->last_active_at->greaterThanOrEqualTo(now()->subMinutes(5));
    }

    /**
     * Human-readable last seen label: "Active now", "Active 2h ago", "Offline".
     */
    public function getLastSeenLabelAttribute(): string
    {
        if (! $this->last_active_at) {
            return 'Offline';
        }

        if ($this->isOnline()) {
            return 'Active now';
        }

        return 'Active ' . $this->last_active_at->diffForHumans();
    }

    public function isParishAdministrator(): bool
    {
        $pos = strtolower($this->position ?? '');
        $name = strtolower($this->name ?? '');
        $email = strtolower($this->email ?? '');
        $role = strtolower($this->role ?? '');

        return str_contains($pos, 'parish administrator')
            || str_contains($name, 'parish administrator')
            || $email === 'admin@pilarshrine.test'
            || (in_array($role, ['super_admin', 'admin'], true) && !str_contains($pos, 'secretary') && !str_contains($role, 'secretary'));
    }

    public function getRoleBadgeLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin', 'admin' => 'Super Admin',
            'parish_priest' => 'Parish Priest',
            'parochial_vicar' => 'Parochial Vicar',
            'parish_secretary' => 'Parish Secretary',
            'commission_admin' => 'Commission Coordinator',
            'commission_member' => 'Commission Member',
            'staff' => 'Staff',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }

    public function getOrganizationLabelAttribute(): string
    {
        return match ($this->organization) {
            'parish_administration' => 'Parish Administration',
            'commission' => 'Commission',
            'ministry' => 'Ministry',
            'parishioner' => 'Parishioner',
            default => $this->isParishAdministration() ? 'Parish Administration' : ($this->commission_id ? 'Commission' : 'Parishioner'),
        };
    }

    public function getResponsibilitiesLabelAttribute(): string
    {
        if (! empty($this->responsibilities)) {
            return $this->responsibilities;
        }

        return match ($this->role) {
            'super_admin', 'admin' => 'Parish Administration & System Management',
            'parish_priest' => 'Parish Oversight & Pastoral Care',
            'parochial_vicar' => 'Liturgical & Pastoral Care',
            'parish_secretary' => 'Administrative Staff',
            'commission_admin' => 'Commission Coordinator',
            'commission_member', 'staff' => 'Commission Member',
            default => 'Parishioner',
        };
    }

    public function getCommissionMinistrySummaryAttribute(): string
    {
        // 1. Explicit parish-wide commission oversight (Super Admin, Priest, Vicar, or explicit all_commissions perm)
        // Parish Secretary is administrative staff and does NOT default to "All Commissions"!
        if ($this->hasParishWideCommissionOversight()) {
            return 'All Commissions';
        }

        // 2. Collect connected commissions and ministries
        $connected = [];
        if ($this->commission) {
            $connected[] = $this->commission->name;
        }
        foreach ($this->commissions as $comm) {
            if (! in_array($comm->name, $connected, true)) {
                $connected[] = $comm->name;
            }
        }
        foreach ($this->ministries as $min) {
            if (! in_array($min->name, $connected, true)) {
                $connected[] = $min->name;
            }
        }

        if (! empty($connected)) {
            return implode(', ', $connected);
        }

        return '—';
    }
}
