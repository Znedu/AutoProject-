<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SupportTicketAttachment extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'disk',
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->file_path
            ? Storage::disk($this->disk)->url($this->file_path)
            : null);
    }

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }
}
