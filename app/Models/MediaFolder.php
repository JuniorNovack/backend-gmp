<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaFolder extends Model
{
    protected $fillable = ['name', 'parent_id', 'owner_id', 'is_system'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($folder) {
            if ($folder->files()->exists()) {
                throw new \Exception("Cannot delete folder with files");
            }
        });
    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'folder_id');
    }
}
