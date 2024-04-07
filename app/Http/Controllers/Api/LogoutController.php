<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
            $User_id = Auth::user()->id;
            $token = PersonalAccessToken::findToken($User_id);
            if($token !== null){
                $token->delete();
                return response()->json([
                    'Success' => true,
                    'message' => 'successfully logged out'
                ],200);
            }

    }
}
