<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller{

    public function migrate(){
        DB::beginTransaction();
        try {
            Artisan::call('migrate', ['--force' => true]);
            return redirect()->route('login')->with('message','Migrations Complete!');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error running migrations: ' . $e->getMessage()]);
        }
    }
    

}
