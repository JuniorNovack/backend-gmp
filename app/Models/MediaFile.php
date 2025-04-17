<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaFile extends Model
{
    protected $fillable = [
        'name', 'path', 'folder_id', 'owner_id', 
        'type', 'duration', 'metadata','url'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'base_template_id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(SharePermission::class, 'media_file_id');
    }
}
