<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

class GeneratedDescription extends Model
{
    use HasFactory;

    protected $hidden =[
        'prompt_settings',
    ];

    protected $fillable = [
        'title',
        'description',
        'prompt_settings',
    ];

    protected $casts = [
        'prompt_settings' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
