<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
