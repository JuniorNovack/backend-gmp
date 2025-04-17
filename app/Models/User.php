<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\PasswordReset;
use App\Models\Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\PersonalAccessToken;
use Laravel\Sanctum\NewAccessToken;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\ResendPinRegisterUserNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, BaseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'last_activity',
        'password',
    ];

    protected $cacheableRelations = ['roles', 'company'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'manager_id');
    }

    /**
     * @inheritDoc
     */
    public function createToken(string $name, array $abilities = ['*']): NewAccessToken
    {
        /**
         * @var PersonalAccessToken $token
         */
        $token = $this->tokens()->create(
            [
                'name' => $name,
                'token' => hash('sha256', $plainTextToken = Str::random(40)),
                'abilities' => $abilities,
                'expires_at' => now()->addMinutes(config('sanctum.expiration'))
            ]
        );

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    public function passwordResets(): MorphMany
    {
        return $this->morphMany(PasswordReset::class, 'resetable');
    }

    public function createPin(): PasswordReset
    {
        /**
         * @var Carbon $expirationTime
         */
        $expirationTime = Carbon::now()->addDays(5);

        /**
         * @var PasswordReset $passwordReset
         */
        $passwordReset = $this->passwordResets()->create(
            [
                'contact' => $this->getContactDetail(),
                'expires_at' => $expirationTime
            ]
        );

        return $passwordReset;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }


    public function SendPinNotification($token)
    {
        $this->notify(new ResendPinRegisterUserNotification($token));
    }
}
