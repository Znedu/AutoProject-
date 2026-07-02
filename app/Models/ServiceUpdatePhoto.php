<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceUpdatePhoto extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'service_update_id',
        'disk',
        'file_path',
        'caption',
        'sort_order',
    ];

    public function serviceUpdate(): BelongsTo
    {
        return $this->belongsTo(ServiceUpdate::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->file_path
            ? '/storage/' . $this->file_path
            : null);
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
