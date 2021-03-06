<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\AuthenticateRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\User\UserIsBlockedException;
use App\Exceptions\User\UserEmailNotVerifiedException;

class LoginController extends Controller
{
    public function authenticate(AuthenticateRequest $request, UserService $userService)
    {
        try {
            $user = $userService->getByEmail($request->get("email"));

            if ($user->is_blocked) {
                throw new UserIsBlockedException(__("auth.blocked"));
            }

            if (!$user->is_email_verified) {
                throw new UserEmailNotVerifiedException(__("auth.email_not_verified"));
            }

            if (!Hash::check($request->get("password"), $user->password)) {
                throw new Exception(__("auth.invalid_credentials"));
            }

            $token = $user->createToken("user-details");

            return response()->json([
                "status"    => "success",
                "message"   => __("auth.success"),
                "data"      => [
                    "user"  => $user,
                    "token" => $token->plainTextToken
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "status"    => "error",
                "message"   => $e->getMessage(),
                "errors"    => [
                    "email" => [$e->getMessage()]
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function logout()
    {
        request()->user()->tokens()->delete();

        return response()->json([
            "status"    => "success",
            "message"   => __("auth.logout_success"),
        ], Response::HTTP_OK);
    }
}
