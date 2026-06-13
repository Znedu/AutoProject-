<?php

namespace App\Models;

use App\Enums\RoleSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->relationLoaded('permissions')) {
            return $this->permissions->contains('slug', $permissionSlug);
        }

        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    public function slugEnum(): ?RoleSlug
    {
        return RoleSlug::tryFrom($this->slug);
    }
}
