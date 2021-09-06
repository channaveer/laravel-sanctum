<?php

namespace App\Http\Controllers\Auth;

use App\Events\ResetPasswordEvent;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\VerifyResetPasswordRequest;
use App\Services\PasswordResetService;
use App\Services\UserService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordController extends Controller
{
    public function verifyResetPassword(VerifyResetPasswordRequest $request, PasswordResetService $passwordResetService)
    {
        try {
            $passwordResetService->getByEmailAndToken($request->get("email"), $request->get("token"));

            return response()->json([
                "status"    => "success",
                "message"   => __("auth.account_verified"),
                "data"      => [
                    "email" => $request->get("email"),
                    "token" => $request->get("token")
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "status"    => "error",
                "message"   => $e->getMessage(),
                "errors"    => [
                    "token" => [$e->getMessage()]
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function resetPassword(
        ResetPasswordRequest $request,
        PasswordResetService $passwordResetService,
        UserService $userService
    ) {
        try {
            $passwordResetService->getByEmailAndToken($request->get("email"), $request->get("token"));

            $user = $userService->getByEmail($request->get("email"));
            $user->password = $request->get("password");
            $user->save();

            $passwordResetService->deleteTokensByEmail($user->email);

            //ToDo:: Add listener and mail
            event(new ResetPasswordEvent($user));

            return response()->json([
                "status"    => "success",
                "message"   => __("auth.password_reset_success"),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "status"    => "error",
                "message"   => $e->getMessage(),
                "errors"    => [
                    "token" => [$e->getMessage()]
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
