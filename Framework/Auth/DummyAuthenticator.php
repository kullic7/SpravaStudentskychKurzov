<?php

namespace Framework\Auth;

use Framework\Core\App;
use Framework\Core\IIdentity;
use App\Models\User as AppUserModel;
use App\Models\LoggedUser;

/**
 * Class DummyAuthenticator
 * Authenticate users against the `users` table and return a LoggedUser identity.
 */
class DummyAuthenticator extends SessionAuthenticator
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    protected function authenticate(string $username, string $password): ?IIdentity
    {
        // find user by email
        $items = AppUserModel::getAll('email = ?', [$username], null, 1);
        $user = $items[0] ?? null;


        if ($user === null) {
            return null;
        }

        // Password is stored in DB as password_hash (use password_verify)
        if (!isset($user->passwordHash) || !password_verify($password, $user->passwordHash)) {
            return null;
        }

        // Return a lightweight identity for session storage
        return new LoggedUser(
            $user->id,
            $user->email ?? '',
            $user->firstName ?? '',
            $user->lastName ?? '',
            $user->role ?? ''
        );
    }
}
