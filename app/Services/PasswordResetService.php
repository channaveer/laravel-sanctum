<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\PasswordReset;
use Exception;

class PasswordResetService
{
    public function createToken($email)
    {
        return PasswordReset::create([
            'email' => $email,
            'token' => Str::uuid()
        ]);
    }

    public function getByEmailAndToken($email, $token)
    {
        $passwordReset = PasswordReset::where(['email' => $email, 'token' => $token])->first();

        if (!$passwordReset) {
            throw new Exception('Password reset token details not found.');
        }

        return $passwordReset;
    }

    public function deleteTokensByEmail($email)
    {
        return PasswordReset::where("email", $email)->delete();
    }
}
