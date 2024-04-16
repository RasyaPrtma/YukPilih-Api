<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Choices;
use App\Models\Divisons;
use App\Models\Polls;
use App\Models\User;
use App\Models\Votes;
use Illuminate\Console\View\Components\Choice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class   PollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Auth::user()->id;
            $poll = Polls::all('id', 'title', 'description','deadline', 'created_by', 'created_at');
            foreach ($poll as $polling) {
                $user = User::where('id', $polling->created_by)->first();
                if ($user !== null) {
                    $polling->created_by = $user->username;
                }
            }
            if ($poll !== null) {
                $votes = Votes::all()->groupBy('division_id');
                $choice = Choices::all();
                $choice_vote = [];
                if(count($votes) > 0 && count($choice) > 0) {
                    foreach($choice as $choices){
                        $choice_vote[$choices->id] = 0;
                    }
                    foreach ($votes as $vote) {
                        $temporary_votes = [];
                        foreach ($vote as $voteItem) {
                        $voteCount = 0;
                            foreach ($choice as $choices) {
                                if($voteItem->choice_id == $choices->id){
                                    $voteCount++;
                                }
                            }
                            $temporary_votes[$voteItem->choice_id] = $voteCount;
                        }
                        $max_val = max($temporary_votes);
                        $filter = collect($temporary_votes)->filter(function ($val) use ($max_val){
                            return $val == $max_val;
                        })->all();
                        $pointCalculate = 1/count($filter);
                        foreach($filter as $key => $value){
                            $choice_vote[$key] += $pointCalculate;
                        }
                    }
                    $result = [];
                    $num = 0;
                    foreach($choice_vote as $key => $val){
                        $num += $val;
                    }
                    foreach($choice_vote as $key => $val){
                        $choice_vote[$key] = $val / $num * 100;
                        $choice = Choices::find($key);
                        $result[] = [
                            'poll_id' => $choice->poll_id,
                            'choice' => $choice->choice,
                            'points' => round($choice_vote[$key]),
                        ];
                    }
                }
                return response()->json([
                    'Success' => true,
                    'data_poll' => $poll,
                    'data_result' => $result ?? [],
                    'data_choices' => Choices::all() ?? []
                ], 200);
            }
            return response()->json(['message' => 'Data Not Finded'], 404);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user_id = Auth::user()->id;
        $is_admin = User::find($user_id);
        if ($is_admin->is_admin > 0) {
            // make validation
            $validator = Validator::make($request->all(), [
                'title' => 'string|required',
                'description' => 'string|required',
                'deadline' => 'date|required',
                'choices' => 'required|array|unique:App\Models\Choices,choice|min:2',
                'choices.*' => 'required|string|min:2'
            ]);

            // check if fails
            if ($validator->fails()) {
                return response([
                    'message' => 'The Given Data Was Invalid',
                    'errors' => $validator->errors()
                ], 422);
            }

            // create poll
            $poll = Polls::create([
                'title' => $request->title,
                'description' => $request->description,
                'deadline' => $request->deadline,
                'created_by' => Auth::user()->id
            ]);

            if ($poll) {
                // create Choice
                foreach ($request->choices as $choice) {
                    Choices::create([
                        'choice' => $choice,
                        'poll_id' => $poll->id
                    ]);
                }
                // return response
                return response()->json([
                    'Success' => true,
                    'message' => [
                        'data' => $poll
                    ]
                ], 201);
            }
        }
        return response()->json(['message' => 'Unauthorized.'], 401);
    }
    public function indexId(string $id)
    {
        $user = Auth::user()->id;
        $user_info = User::find($user);
        $user_vote = Votes::where('user_id', $user)->first();
        if ($user_info->is_admin !== 0 || $user_vote !== null) {
            $poll = Polls::find($id);
            if ($poll !== null) {
                $data_choice = Choices::where('poll_id', $poll->id)->get();
                $admin = User::all();
                $arr_admin = [];
                $choices = Choices::where('poll_id', $poll->id)->get();
                $division = Votes::where('poll_id', $id)
                    ->get()
                    ->groupBy('division_id');
                $choice_point = [];
                $username_admin = "";
                if(count($data_choice) > 0 && count($division) > 0) {
                       foreach ($admin as $data) {
                    if ($data->is_admin > 0) {
                        array_push($arr_admin, $data);
                    }
                }
                foreach ($arr_admin as $admin) {
                    if ($poll->created_by === $admin->id) $username_admin = $admin->username;
                }
 
                foreach ($choices as $choice) {
                    $choice_point[$choice->id] = 0;
                }

                foreach ($division as $data) {
                    $temporary_voteCount = [];
                    foreach ($choices as $choice) {
                        $vote = 0;
                        foreach ($data as $dataVote) {
                            if ($dataVote->choice_id == $choice->id) {
                                $vote++;
                            }
                        }
                        $temporary_voteCount[$choice->id] = $vote;
                    }
                    $max = max($temporary_voteCount);
                    $search = collect($temporary_voteCount)->filter(function ($val) use ($max) {
                        return $val ==  $max;
                    })->all();
                    $point = 1 / count($search);
                    foreach ($search as $key => $value) {
                        $choice_point[$key] += $point;
                    }
                }
                $jml = 0;
                foreach ($choice_point as $key => $value) {
                    $jml += $value;
                }
                $result = [];
                foreach ($choice_point as $key => $value) {
                    $Choice = Choices::find($key);
                    $choice_point[$key] = $value / $jml * 100;
                    $result[] = [
                        'poll_id' => $choice->poll_id,
                        'choice' => $Choice->choice,
                        'points' =>  round($choice_point[$key]),
                    ];
                }
                }
                return response()->json([
                    'Success' => true,
                    'message' => [
                        'data_poll' => [
                            'id' => $poll->id,
                            'title' => $poll->title,
                            'description' => $poll->description,
                            'deadline' => $poll->deadline,
                            'created_by' => $poll->created_by,
                            'created_at' => $poll->created_at,
                            'creator' => $username_admin,
                        ],
                        'data_result' => $result ?? [],
                        'data_choices' => $data_choice ?? []
                    ]
                ], 200);
            }
            return response(['message' => 'Data Not Finded!'],404);
        }
        return response(['message' => 'Unauthorized.'], 422);
    }

    public function Delete(string $id)
    {
        $user_id = Auth::user()->id;
        $is_admin = User::find($user_id);
        if ($is_admin->is_admin !== 0) {
            $poll = Polls::where('id', $id)->first();
            if ($poll !== null) {
                $poll->delete();
                return response()->json([
                    'Success' => 'true',
                    'message' => 'Polls Successfully deleted'
                ], 200);
            }
            return response()->json(['message' => 'Data Not Found!'], 404);
        }
        return response()->json(['message' => 'Unauthorized.'], 401);
    }
}
