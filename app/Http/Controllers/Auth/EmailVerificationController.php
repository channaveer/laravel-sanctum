<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\EmailVerificationRequest;
use App\Services\PasswordResetService;
use App\Services\UserService;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationController extends Controller
{
    public function accountVerification(
        EmailVerificationRequest $request,
        PasswordResetService $passwordResetService,
        UserService $userService
    ) {
        try {
            $passwordResetService->getByEmailAndToken($request->get("email"), $request->get("token"));

            $user = $userService->getByEmail($request->get("email"));
            $user->email_verified_at = now();
            $user->save();

            $passwordResetService->deleteTokensByEmail($request->get("email"));

            return response()->json([
                "status"    => "success",
                "message"   => "User account verified successfully",
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
