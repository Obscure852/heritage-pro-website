<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Qualification;
use App\Models\QualificationUser;
use App\Models\User;

class QualificationController extends Controller{

    public function index($id){
        $user = User::find($id);
        $qualifications = Qualification::all();
        return view('staff.add-user-qualification',['user' => $user,'qualifications' => $qualifications]);
    }
    
    public function storeQualification(Request $request){
        $request->validate([
            'user_id' => 'required',
            'college' => 'required|string|max:255',
            'qualification_id' => 'required|integer',
            'level' =>'required|string',
            'start_date' => 'required|date',
            'completion_date' => 'required|date'
        ]);

        QualificationUser::create($request->all());
        return redirect()->back()->with('message', 'Qualification created successfully.');
    }



    public function editQualification($id,$qualificationId){
        $user = User::find($id);
        $qualification = QualificationUser::with('qualification')->where('user_id',$user->id)->where('qualification_id',$qualificationId)->first();
        $qualifications = Qualification::all();
        return view('staff.edit-user-qualification',['user' => $user,'qualification' => $qualification,'qualifications' => $qualifications]);
    }

    public function updateQualification(Request $request,$id){
        $request->validate([
            'user_id' => 'required',
            'college' => 'required|string|max:255',
            'qualification_code' => 'required|integer',
            'start_date' => 'required|date',
            'completion_date' => 'required|date'
        ]);
        $qualification = QualificationUser::find($id);
        $qualification->update($request->all());
        return redirect()->back()->with('message', 'Qualification updated successfully.');
    }

    public function removeQualification($userId, $id){
        $user = User::find($userId);
        $qualification = QualificationUser::withTrashed()
                                          ->where('user_id', $userId)
                                          ->where('qualification_id', $id)
                                          ->first();
        //dd($qualification);
        if ($qualification) {
            $qualification->delete();
            $message = 'Record deleted successfully!';
        } else {
            $message = 'Record not found.';
        }
        return redirect()->back()->with('message', $message);
    }
    


}
