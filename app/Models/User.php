<?php

namespace App\Models;

use App\Enums\RoleSlug;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role_id',
        'status',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function assignedJobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class, 'mechanic_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function assignedSupportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to');
    }

    public function supportTicketReplies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class);
    }

    public function serviceUpdates(): HasMany
    {
        return $this->hasMany(ServiceUpdate::class);
    }

    public function reportExports(): HasMany
    {
        return $this->hasMany(ReportExport::class, 'requested_by');
    }

    public function roleSlug(): string
    {
        if ($this->relationLoaded('assignedRole') && $this->assignedRole !== null) {
            return $this->assignedRole->slug;
        }

        return $this->assignedRole()->value('slug') ?? RoleSlug::Customer->value;
    }

    /**
     * @param  string|list<string>|RoleSlug|list<RoleSlug>  $roles
     */
    public function hasRole(string|array|RoleSlug $roles): bool
    {
        $needles = collect(is_array($roles) ? $roles : [$roles])
            ->map(fn (string|RoleSlug $role) => $role instanceof RoleSlug ? $role->value : $role)
            ->all();

        return in_array($this->roleSlug(), $needles, true);
    }

    public function hasPermission(string $permission): bool
    {
        $this->loadMissing('assignedRole.permissions');

        return $this->assignedRole?->hasPermission($permission) ?? false;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleSlug::Administrator);
    }

    public function isMechanic(): bool
    {
        return $this->hasRole(RoleSlug::Mechanic);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(RoleSlug::Staff);
    }

    public function isCustomer(): bool
    {
        return $this->hasRole(RoleSlug::Customer);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected function initials(): Attribute
    {
        return Attribute::get(function (): string {
            $words = preg_split('/\s+/', trim($this->name)) ?: [];
            $initials = '';

            foreach ($words as $word) {
                $initials .= strtoupper(substr($word, 0, 1));
            }

            return substr($initials, 0, 2);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * @param  string|list<string>|RoleSlug  $role
     */
    public function scopeRole(Builder $query, string|RoleSlug|array $role): Builder
    {
        $slugs = collect(is_array($role) ? $role : [$role])
            ->map(fn (string|RoleSlug $value) => $value instanceof RoleSlug ? $value->value : $value)
            ->all();

        return $query->whereHas('assignedRole', fn (Builder $roleQuery) => $roleQuery->whereIn('slug', $slugs));
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->role(RoleSlug::Customer);
    }

    public function scopeStaff(Builder $query): Builder
    {
        return $query->role(RoleSlug::Staff);
    }

    public function scopeMechanics(Builder $query): Builder
    {
        return $query->role(RoleSlug::Mechanic);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->role(RoleSlug::Administrator);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
