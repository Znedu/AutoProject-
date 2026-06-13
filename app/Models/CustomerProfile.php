<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerProfile extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'address',
        'city',
        'province',
        'postal_code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::get(function (): ?string {
            $parts = array_filter([
                $this->address,
                $this->city,
                $this->province,
                $this->postal_code,
            ]);

            return $parts !== [] ? implode(', ', $parts) : null;
        });
    }
}
