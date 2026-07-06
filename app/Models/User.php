<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'designation_id',
        'cnic',
        'full_name',
        'email',
        'mobile_no',
        'username',
        'password',
        'role_id',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'totp_secret',
    ];

    protected function casts(): array
    {
        return [
            'password'     => 'hashed',
            'is_active'    => 'boolean',
            'last_login_at'=> 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                        */
    /* ------------------------------------------------------------------ */

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    /** All scope assignments (including inactive) */
    public function scopes(): HasMany
    {
        return $this->hasMany(UserScope::class);
    }

    /** Only currently active scope assignments */
    public function activeScopes(): HasMany
    {
        return $this->hasMany(UserScope::class)->where('is_active', true);
    }

    /** Telephony configuration for ZIWO agent integration */
    public function telephonyAgentConfig(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TelephonyAgentConfig::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Permission Helpers                                                   */
    /* ------------------------------------------------------------------ */

    /**
     * Check if the user has a named permission via their role.
     * Super admins always return true.
     */
    public function hasPermission(string $permission): bool
    {
        // 1. Super admins always return true as specified in docstring
        if ($this->role?->name === Role::SUPER_ADMIN) {
            return true;
        }

        // 2. Per-request cache to avoid N+1 on Blade @can() checks
        static $cache = [];
        $key = $this->id . ':' . $permission;
        return $cache[$key] ??= (
            $this->role?->permissions->contains('name', $permission) ?? false
        );
    }

    /**
     * Whether the user has write access (create/update) for a unit type + optional unit ID.
     *
     * @param string   $type      e.g. 'zone', 'beat', 'call_center'
     * @param int|null $officeId  The specific office ID, or null to check any office of this type
     */
    public function canWriteIn(string $type, ?int $officeId = null): bool
    {
        return $this->activeScopes()
            ->whereHas('office', fn($q) => $q->where('type', $type))
            ->when($officeId !== null, fn($q) => $q->where(function ($q) use ($officeId) {
                $q->where('office_id', $officeId)->orWhereNull('office_id');
            }))
            ->whereIn('access_level', ['read_write', 'full'])
            ->exists();
    }

    /**
     * Whether the user has delete/export access for a unit type + optional unit ID.
     */
    public function canDeleteIn(string $type, ?int $officeId = null): bool
    {
        return $this->activeScopes()
            ->whereHas('office', fn($q) => $q->where('type', $type))
            ->when($officeId !== null, fn($q) => $q->where(function ($q) use ($officeId) {
                $q->where('office_id', $officeId)->orWhereNull('office_id');
            }))
            ->where('access_level', 'full')
            ->exists();
    }

    /**
     * Whether the user has any active scope for a given office type.
     */
    public function hasScope(string $type): bool
    {
        return $this->activeScopes()->whereHas('office', fn($q) => $q->where('type', $type))->exists();
    }

    /**
     * Get all office IDs the user has access to for a given type.
     * Returns null if the user has a "all of type" scope (office_id = null).
     *
     * @return array<int>|null  null = unrestricted within this type
     */
    public function getScopeUnitIds(string $type): ?array
    {
        $scopes = $this->activeScopes()->whereHas('office', fn($q) => $q->where('type', $type))->get();
        if ($scopes->isEmpty()) {
            return [];
        }
        // If any scope has null office_id, user has access to ALL units of this type
        if ($scopes->contains(fn($s) => $s->office_id === null)) {
            return null;
        }
        return $scopes->pluck('office_id')->all();
    }

    /**
     * Get the designated landing page route name based on user role.
     */
    public function getLandingPageRoute(): string
    {
        // 1. Check primary role-based preferences first (if authorized)
        $role = $this->role?->name;
        $primary = match ($role) {
            'agent'           => $this->hasPermission('calls.create') ? route('calls.create') : null,
            'operation_admin' => $this->hasPermission('users.view') ? route('admin.users.index') : null,
            'super_admin'     => $this->hasPermission('dashboard.view') ? route('dashboard') : null,
            'sector_admin', 
            'zone_admin', 
            'agent_supervisor', 
            'beat_operator', 
            'operation'       => $this->hasPermission('calls.view') ? route('calls.index') : null,
            default           => null
        };

        if ($primary) return $primary;

        // 2. Systematic fallback scan across the registry hierarchy
        $fallbacks = [
            'dashboard.view'           => 'dashboard',
            'calls.create'             => 'calls.create',
            'calls.view'               => 'calls.index',
            'reports.view'             => 'reports.call-type-summary',
            'users.view'               => 'admin.users.index',
            'geography.offices.view'   => 'admin.offices.index',
            'geography.carriageways.view' => 'admin.carriageways.index',
            'roles.view'               => 'admin.roles.index',
            'system.settings.view'     => 'admin.settings.index',
            'system.audit_view'        => 'admin.audit.index',
        ];

        foreach ($fallbacks as $permission => $routeName) {
            if ($this->hasPermission($permission)) {
                try {
                    return route($routeName);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // 3. Absolute fallback: If no operational permissions, send to login or a public 'profile'
        // If they are already authenticated, dashboard is the last stand (middleware will handle the final block)
        return route('dashboard');
    }

    /**
     * Get the human-readable role title.
     */
    public function getRoleTitle(): string
    {
        $role = $this->role?->name;

        return match ($role) {
            'super_admin'     => 'System Administrator',
            'operation_admin', 'operation' => 'Operation Manager',
            'agent'           => 'Call Center Agent',
            'agent_supervisor'=> 'Shift Supervisor',
            'zone_admin', 'sector_admin' => 'Zone and Sector Incharge',
            'beat_operator'   => 'Wireless Operator',
            default           => 'System Commander',
        };
    }
}

