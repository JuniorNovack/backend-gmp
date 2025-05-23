<?php

namespace App\Models;

use App\Models\BaseM;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\Email\VerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Notifications\Phone\MustVerifyPhoneNumber;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

abstract class Authenticatable extends BaseM implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use \Illuminate\Auth\Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, MustVerifyPhoneNumber;

    /**
     * @inheritDoc
     */
    public function sendEmailVerificationNotification()
    {
        // do not send notification if the user has no email address
        if (!$this->getEmailForVerification() || !method_exists($this, 'notify')) {
            return;
        }
        $this->notify(new VerifyEmail());
    }


    public function getContactDetail(): ?string
    {
        return $this->getEmailForVerification() ?? $this->getPhoneNumberForVerification();
    }

    /**
     * Checks if user has verified his email address or phone number
     *
     * @return bool
     */
    public function hasVerifiedAccount(): bool
    {
        return $this->hasVerifiedEmail() || $this->hasVerifiedPhoneNumber();
    }
}
