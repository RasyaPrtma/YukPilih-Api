<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //make validation
        $validator = Validator::make($request->all(),[
            'username' => 'required',
            'password' => 'required|confirmed'
        ]);

        // check if validation fails
        if($validator->fails()){
            return response($validator->errors(),401);
        }

        // Check If username valid
        $user = User::where('username',$request->username)->first();
        $token = $user->createToken('token-name', ['*'], now()->addHours(24))->plainTextToken;
        if($user && Hash::check($request->password,$user->password)){
            return response()->json([
                'Success' => true,
                'message' => [
                    'User' => $user,
                    'Token' => $token
                ]
            ],200);            
        }

        return response()->json([
            'Success' => false,
            'message' => [
                'User' => 'Username/Password Not Match'
            ]
        ],401);
    }
}
