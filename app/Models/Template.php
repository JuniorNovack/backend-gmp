<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $fillable = ['base_template_id','name','category','duration','customizable','status', 'custom_data', 'created_by'];

    protected $casts = [
        'custom_data' => 'array',
    ];

    public function baseTemplate(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'base_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
