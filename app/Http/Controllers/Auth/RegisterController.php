<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserRegisteredEvent;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\User\RegisterUserRequest;
use App\Services\PasswordResetService;

class RegisterController extends Controller
{
    public function register(RegisterUserRequest $request, PasswordResetService $passwordResetService)
    {
        try {
            $user = User::create($request->validated());

            $passwordReset = $passwordResetService->createToken($request->get("email"));

            //ToDo::Create appropriate listener
            event(new UserRegisteredEvent($user, $passwordReset));

            return response()->json([
                "status"    => "success",
                "message"   => __("auth.registration_success"),
                "data"      => [
                    "user"  => $user,
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "status"    => "error",
                "message"   => $e->getMessage(),
                "errors"    => [
                    "password" => [$e->getMessage()]
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
