<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ForgotPasswordRequest;
use App\Jobs\ResetPasswordJob;
use App\Services\PasswordResetService;
use App\Services\UserService;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(
        ForgotPasswordRequest $request,
        PasswordResetService $passwordResetService,
        UserService $userService
    ) {
        $passwordReset = $passwordResetService->createToken($request->get("email"));

        $user = $userService->getByEmail($request->get("email"));

        // ToDo:: Work on dispatcher
        ResetPasswordJob::dispatch($user, $passwordReset);

        return response()->json([
            "status" => "success",
            "message" => __("auth.password_reset_mail_success"),
        ]);
    }
}
