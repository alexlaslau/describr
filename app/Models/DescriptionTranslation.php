<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescriptionTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'generated_description_id',
        'target_language',
        'source_language',
        'provider',
        'status',
        'translated_text',
        'error_message'
    ];

    protected $casts = [
        'translated_at' => 'datetime',
    ];

    public function generatedDescription(): BelongsTo
    {
        return $this->belongsTo(GeneratedDescription::class);
    }
}
