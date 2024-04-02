<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class ResetPasswordController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
            $user = Auth::user()->id;
            $old_password= User::find($user);
            if(Hash::check($request->old_password,$old_password->password)){
                $old_password->password = Hash::make($request->new_password);
                $old_password->save();
                PersonalAccessToken::findToken($request->bearerToken())->delete();
                return response()->json([
                    'message' => 'Reset Password Successfully, User Logged Out',
                ],200);
            }
            return response()->json([
                'message' => 'Old Password Did Not Match'
            ],422);
    }
}
