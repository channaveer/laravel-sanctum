<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AuthenticateRequest;
use App\Http\Requests\RegisterUserRequest;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function authenticate(AuthenticateRequest $request)
    {
        try {
            $user = User::where('email', $request->get('email'))->first();

            if (!Hash::check($request->get('password'), $user->password)) {
                throw new Exception('Invalid email/password.');
            }

            $token = $user->createToken('user-details');

            return response()->json([
                'status'    => 'success',
                'message'   => 'User authenticated successfully',
                'data'      => [
                    'user'  => $user,
                    'token' => $token->plainTextToken
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => $e->getMessage(),
                'errors'    => [
                    'password' => [$e->getMessage()]
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function register(RegisterUserRequest $request)
    {
        try {
            $user = User::create($request->validated());

            return response()->json([
                'status'    => 'success',
                'message'   => 'User registered successfully',
                'data'      => [
                    'user'  => $user,
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => $e->getMessage(),
                'errors'    => [
                    'password' => [$e->getMessage()]
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function logout()
    {
        request()->user()->tokens()->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'User logged out successfully',
        ], Response::HTTP_OK);
    }
}
