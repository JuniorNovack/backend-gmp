<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory, BaseModel;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'manager_id',
        'screens_allowed',
        'active',
    ];

    protected $cacheableRelations = ['manager'];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
