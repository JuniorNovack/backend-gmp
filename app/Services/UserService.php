<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Services\BaseService;
use App\Exceptions\ModelException;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Services\Contracts\IUserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UserService extends BaseService implements IUserService
{
    private function initializeModel(): void
    {
        $this->setModel(new User());
    }

    /**
     * @inheritDoc
     */
    protected function getModelObject(): User
    {
        $this->initializeModel();
        return $this->getModel();
    }

    /**
     * @inheritDoc
     */
    public function getUserByEmail(string $email): User
    {
        return $this->findModelBy('email', $email);
    }

    /**
     * @inheritDoc
     */
    public function destroySession(User $user): void
    {
        $user->forceFill(['last_activity' => now()]);
        $this->updateUser($user);

        if (!$user->tokens()->delete()) {
            throw new ModelException('Failed to delete token');
        }
    }

    /**
     * @inheritDoc
     */
    public function destroyProfile(User $user): void
    {
        $this->delete($user);
    }

    /**
     * @inheritDoc
     */
    public function createAccount(User $user): User
    {
        $model = $this->insert($user);
        $model->createPin();

        event(new Registered($user));

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function resentActivationCode(User $user): User
    {
        if ($user->passwordResets()->exists()) {
            if (!$user->passwordResets()->delete()) {
                throw new ModelException('Failed to delete last user pin');
            }
        }

        $pin = $user->createPin();

        $user->SendPinNotification($pin->token);
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function generateUserTokenFromIp(User $user, string $ip, $notify = false): array
    {
        if ($notify) {
            $pin =  $user->createPin();
            $user->sendPasswordResetNotification($pin->token);
        }

        $token = $user->createToken($ip);

        /** @var PersonalAccessToken $accessToken */
        $accessToken = $token->accessToken;

        $user->forceFill(['last_activity' => null]);
        $userResult = $this->updateUser($user);

        return [
            'token' => [
                'value' => $token->plainTextToken,
                'type' => 'bearer',
                'expires_at' => blank($accessToken->expires_at) ? $accessToken->expires_at : $accessToken->expires_at->toDateTimeString()
            ],
            'user' => $userResult,
        ];
    }

    /**
     * @inheritDoc
     */
    public function hasValidCredentials(string $email, string $password): bool
    {
        $credentials = ['email' => $email, 'password' => $password];

        return Auth::attempt($credentials);
    }

    /**
     * @inheritDoc
     */
    public function getUserFromGuard(): User
    {
        $model = $this->findModelById(Auth::user()->id);
        $this->setModel($model);

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function getLastActivationCode(User $user): int
    {
        $activationCode = $user->passwordResets->last()->token ?? null;
        if (!$activationCode) {
            throw new ModelNotFoundException('No activation code found');
        }

        return $activationCode;
    }

    /**
     * @inheritDoc
     */
    public function markAccountAsVerified(User $user): void
    {
        if (!$user->passwordResets()->delete()) {
            throw new ModelException('Failed to delete user pins');
        }

        if (!$user->markEmailAsVerified()) {
            throw new ModelException('Failed to mark user email or phone as verified');
        }
    }

    /**
     * @inheritDoc
     */
    public function updateUser(User $user): User
    {
        return $this->update($user);
    }

    /**
     * @inheritDoc
     */
    public function changeUserPassword(array $passwordsInformations): User
    {
        $oldPassword = $passwordsInformations['old_password'];
        $newPassword = $passwordsInformations['password'];

        $user = $this->findModelById(Auth::user()->id);

        if (!Hash::check($oldPassword, $user->password)) {
            throw new \Exception('old password is incorrect.');
        }

        $user->password = Hash::make($newPassword);

        return $this->updateUser($user);
    }
}
