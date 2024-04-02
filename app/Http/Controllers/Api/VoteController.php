<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Choices;
use App\Models\Polls;
use App\Models\User;
use App\Models\Votes;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class VoteController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $poll_id, string $choice_id)
    {
        date_default_timezone_set('Asia/Jakarta');
        $user = Auth::user()->id;
        $is_admin = User::where('id',$user)->first();
        if($is_admin->is_admin < 1){
            $vote = Votes::where('user_id',$user)->first();
            $poll = Polls::find($poll_id);
            $choice = Choices::find($choice_id);
            $deadline = Carbon::parse(new DateTime($poll->deadline))->format('Y-m-d H:i:s A');
            $date_now = date('Y-m-d H:i:s A');
            if($poll !== null && $choice !== null){
                if($vote === null){
                    if($vote === null && $date_now < $deadline){
                        $vote = Votes::create([
                         'choice_id' => intval($choice_id),
                         'user_id' => $user,
                         'poll_id' => intval($poll_id),
                         'division_id' => $is_admin->divisons_id
                        ]); 
     
                        return response()->json([
                         'Success' => true,
                         'message' => [
                             'data' => $vote
                         ]
                        ],200);
                     }
                     return response()->json(['message' => 'Voting Deadline: ' . $deadline],422);
                }
                return response()->json(['message' => 'You Already Vote!'],422);
            }
            return response()->json(['message' => 'Invalid Choice'],422);
        }
        return response()->json(['message' => 'Unauthorized.'],401);
    }
}
