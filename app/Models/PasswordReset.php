<?php

namespace App\Models;

use App\Models\BaseM;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PasswordReset extends BaseM
{
    use BaseModel;

    protected $table = 'password_resets';

    protected $fillable = [
        'contact',
        'expires_at',
        'token'
    ];

    public function resetable(): MorphTo
    {
        return $this->morphTo();
    }

    public function save(array $options = []): bool
    {
        $this->token ??= sprintf("%06d", mt_rand(1, 999999));

        return parent::save($options);
    }
}
