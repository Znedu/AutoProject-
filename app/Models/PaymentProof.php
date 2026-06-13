<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentProof extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'payment_id',
        'disk',
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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
