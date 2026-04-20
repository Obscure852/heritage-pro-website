<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WorkHistory;

class WorkHistoryController extends Controller{

    public function storeWorkHistory(Request $request){
        $request->validate([
            'user_id' => 'required',
            'workplace' => 'required|string|max:255',
            'type_of_work' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        WorkHistory::create($request->all());
        return redirect()->back()->with('message', 'Work History created successfully.');
    }


    public function addWorkHistory($id){
        $user = User::find($id);
        return view('staff.add-user-work-history',['user' => $user]);
    }


    public function removeWorkHistory($id){
        $work = WorkHistory::find($id);
        $work->delete();
        return redirect()->back()->with('message','Record deleted successfully!');
    }

    public function editWorkHistory($id,WorkHistory $work){
        $user = User::find($id);
        return view('staff.edit-user-work-history',['user' => $user, 'work' => $work]);
    }

    public function updateWorkHistory(Request $request,$id){
        $request->validate([
            'user_id' => 'required',
            'workplace' => 'required|string|max:255',
            'type_of_work' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'required|date'
        ]);
        $work_history = WorkHistory::find($id);
        $work_history->update($request->all());
        return redirect()->back()->with('message', 'Work History updated successfully.');
    }
}
