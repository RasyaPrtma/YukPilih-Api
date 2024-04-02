<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //Validation
        $validator = Validator::make($request->all(),[
            'username' => 'required|unique:users',
            'password' => 'required|min:5|confirmed',
            'role' => 'required',
            'divisions' => 'required'
        ]);

        // check validation if fails
        if($validator->fails()){
            return response($validator->errors(),400);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'divisons_id' => $request->divisions
        ]);

        if($user){
            return response()->json([
                'Success' => true,
                'message' => [
                    'User' => $user
                ]
            ],201);
        }

        return response()->json([
            'Success' => false,
            'message' => [
                'User' => 'Failed Created User'
            ]
        ],209);
    }
}
