<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class MeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
            $token = Auth::user()->id;
            return response()->json([
                'Success' => true,
                'message' => [
                    'User' => User::where('id',$token)->first()
                ]
            ],200);
    }
}
