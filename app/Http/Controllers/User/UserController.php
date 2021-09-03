<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function show()
    {
        return response()->json([
            'status'    => 'success',
            'message'   => 'User data found successfully',
            'data'      => [
                'user' => request()->user()
            ]
        ], Response::HTTP_OK);
    }
}
