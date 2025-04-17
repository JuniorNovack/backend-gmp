<?php

namespace App\Services\Contracts;

use App\Models\User;
use App\Exceptions\ModelException;
use App\Services\Contracts\IBaseService;
use App\Exceptions\ModelNotFoundException;

interface IUserService extends IBaseService
{
    /**
     * Finds a user by its email
     *
     * @param string $email User email
     * @return User
     * @throws ModelNotFoundException Throws <b>ModelNotFoundException</b> exception if email cannot be found
     */
    public function getUserByEmail(string $email): User;

    /**
     * Finds a user using auth guard
     *
     * @return User
     * @throws ModelNotFoundException Throws <b>ModelNotFoundException</b> exception in case of empty result
     */
    public function getUserFromGuard(): User;

    /**
     * Invalidates user session
     *
     * @throws ModelException
     */
    public function destroySession(User $user): void;

    /**
     * Creates an account and sends a pin to the user via email or sms in order to complete his registration
     *
     * @param User $user
     * @return User
     * @throws ModelException
     */
    public function createAccount(User $user): User;

    /**
     * Generates a bearer token
     *
     * @param User $user User model
     * @param string $ip User ip
     * @param bool $notify
     * @param bool $account
     * @return array{token: array{value: string, type: string, expire_at: string}, auth_user: object{User}}
     * @throws ModelNotFoundException
     */
    public function generateUserTokenFromIp(User $user, string $ip, $notify = false): array;

    /**
     * Checks if the provided credentials match with our records
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function hasValidCredentials(string $email, string $password): bool;

    /**
     * Deletes all activation codes generated for the user and mark its account as verified
     *
     * @param User $user
     * @return void
     * @throws ModelException
     */
    public function markAccountAsVerified(User $user): void;

    /**
     * Finds user last activation code
     *
     * @param User $user
     * @return int
     * @throws ModelNotFoundException
     */
    public function getLastActivationCode(User $user): int;

    /**
     * resends a pin to the user via email or sms if he not receive a first notification
     *
     * @param User $user
     * @return User
     * @throws ModelException
     */
    public function resentActivationCode(User $user): user;

    /**
     * update user information
     *
     * @param User $user
     * @return User
     * @throws ModelNotFoundException
     */
    public function updateUser(User $user): User;

    /**
     * change user password
     *
     * @param array $passwordsInformations
     * @return User
     * @throws Exception
     */
    public function changeUserPassword(array $passwordsInformations): User;

    /**
     * Delete Profile
     *
     * @param User $user
     * @return void
     * @throws ModelException
     */
    public function destroyProfile(User $user): void;
}
