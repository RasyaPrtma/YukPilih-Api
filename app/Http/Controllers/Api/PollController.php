<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Choices;
use App\Models\Polls;
use App\Models\User;
use App\Models\Votes;
use Illuminate\Console\View\Components\Choice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Auth::user()->id;
        $is_admin = User::find($user_id)->first();
       if($is_admin->is_admin !== 0){
        $poll = Polls::all('id','title','description','created_by','created_at');
        foreach($poll as $polling){
            $user = User::where('id',$polling->created_by)->first();
            if($user !== null){
                $polling->created_by = $user->username;
            }
        }
        if($poll !== null){
        $choice = Choices::all();
        $choice_id = Choices::all('id','choice');
        $arr = [];
        foreach($choice_id as $choice){
        $point_vote = DB::table('votes')->groupBy('choice_id')->select('choice_id',DB::raw('COUNT(choice_id) as points'))->get();
        $point = $point_vote->where('choice_id',$choice->id)->first();
           $obj = [
            'id' => $choice->id,
            'choice' => $choice->choice,
            'points' => $point->points
        ];
        array_push($arr,$obj);
        }
        return response()->json([
            'Success' => true,
            'data_poll' => $poll,
            'data_result' => $arr,
            'data_choices' => Choices::all()
        ],200);
        }
       return response()->json(['message' => 'Data Not Finded'],404);
       }
       return response()->json(['message' => 'Unauthorized'],401);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user_id = Auth::user()->id;
        $is_admin = User::find($user_id)->first();
        if($is_admin->is_admin !== 0){
            // make validation
        $validator = Validator::make($request->all(),[
            'title' => 'string|required',
            'description' => 'string|required',
            'deadline' => 'date|required',
            'choices' => 'required|array|unique:App\Models\Choices,choice|min:2',
            'choices.*' => 'required|string|min:2'
        ]);

        // check if fails
        if($validator->fails()){
            return response([
                'message' => 'The Given Data Was Invalid',
                'errors' => $validator->errors()
            ],422);
        }

        // create poll
        $poll = Polls::create([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'created_by' => Auth::user()->id
        ]);

       if($poll){
         // create Choice
         foreach($request->choices as $choice){
            Choices::create([
                'choice' => $choice,
                'poll_id' => $poll->id
            ]);
        }
        return response()->json([
            'Success' => true,
            'message' => [
                'data' => $poll
            ]
        ],201);
       }
        }
        return response()->json(['message' => 'Unauthorized.'],401);
    }
    public function indexId(string $id){
        $poll = Polls::where('id',$id)->first();
        if($poll !== null){
            $choice = Choices::where('poll_id',$poll->id)->get();
            $admin = User::all();
            $arr_admin = [];
            $username_admin = "";
            foreach($admin as $data){
                if($data->is_admin > 0){
                    array_push($arr_admin,$data);
                }
            }
            foreach($arr_admin as $admin){
                if($poll->created_by === $admin->id) $username_admin = $admin->username;
            }
            $point_vote = DB::table('votes')->groupBy('choice_id')->select('choice_id',DB::raw('COUNT(choice_id) as points'))->get();
            $arr = [];
            foreach($choice as $point){
                $points = $point_vote->where('choice_id',$point->id)->first();
                $obj = [
                    'id' => $point->id,
                    'choice' => $point->choice,
                    'points' => $points->points
                ];
                array_push($arr,$obj);
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
                    'data_result' =>$arr,
                    'data_choices' => $choice
                ]
            ],200);
        }
    }
    
    public function Delete(string $id){
        $user_id = Auth::user()->id;
        $is_admin = User::find($user_id);
        if($is_admin->is_admin !== 0){
            $poll = Polls::where('id',$id)->first();
            if($poll !== null){
                $poll->delete();
                return response()->json([
                    'Success' => 'true',
                    'message' => 'Polls Successfully deletedx'
                ],200);
            }
        }
        return response()->json(['message' => 'Unauthorized.'],401);
    }
}