<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComplainController extends Controller
{
    public function comment_store(Request $request)
    {
        $validated        = Validator::make($request->all(), [

            'reported_user_id' => 'required',
            'reason'           => 'required',

        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 400);
        }

        $dbTables = DB::table('reports');
        //   dd(auth()->user());

        $user_id = auth()->user()->id;
        $reported_user_id = $request->reported_user_id;

        if ($user_id == $reported_user_id) {
            return response()->json(
                [
                    'status'  => true,
                    'message' => 'You cannot report yourself.',
                ],
                400,
            );
        } elseif ($dbTables->where('user_id', $user_id)->where('reported_user_id', $reported_user_id)->count() > 0) {
            return response()->json(
                [
                    'stauts'  => false,
                    'message' => 'You have already reported this user',
                ],
                400
            );
        } else {
            $report = Report::create([

                'user_id'          => $user_id,
                'reported_user_id' => $request->reported_user_id,
                'reason'           => $request->reason,

            ]);
        }

        if (Report::where('reported_user_id', $reported_user_id)->count() > 10) {
            User::find($reported_user_id)->update(['is_banned' => 1]);
        }

        if ($report) {
            return response()->json([

                'status'  => true,
                'message' => 'Report Successfully Saved',
            ], 200);
        } else {
            return response()->json([

                'status'  => false,
                'message' => 'Insert Fail',
            ], 404);
        }
    }
}
