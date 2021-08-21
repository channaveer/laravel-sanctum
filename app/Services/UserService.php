<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\User\UserNotFoundException;

class UserService
{
    public function getByEmail($email)
    {
        $user = User::whereEmail($email)->first();

        if (!$user) {
            throw new UserNotFoundException('Invalid email/password.');
        }

        return $user;
    }
}
