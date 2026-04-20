<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration{
   
    public function up(){
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        try{
            DB::table('roles')->insert(
                [
                    #Administrator role
                    ['name' => 'Administrator','description' => 'For admin and selected staff only'],
        
                    #Admissions roles
                    ['name' => 'Admissions Admin','description' => 'For the admissions staff'],
                    ['name' => 'Admissions Edit','description' => 'For the admissions staff'],
                    ['name' => 'Admissions View','description' => 'For the admissions staff'],

                    ['name' => 'Admissions Health','description' => 'Allows for access to admissions health information'],

                    #Sponsors roles
                    ['name' => 'Sponsors Admin','description' => 'For Sponsors Admin only'],
                    ['name' => 'Sponsors Edit','description' => 'For Sponsors Edit only'],
                    ['name' => 'Sponsors View','description' => 'For Sponsors view only'],

                    #Students roles
                    ['name' => 'Student Admin','description' => 'For student module admin privileges'],
                    ['name' => 'Student Edit','description' => 'For student module admin privileges'],
                    ['name' => 'Student View','description' => 'For students view only'],

                    ['name' => 'Students Health','description' => 'For allowing access to students health data'],
        
                    #Staff roles
                    ['name' => 'HR Admin','description' => 'For HR administrators only'],
                    ['name' => 'HR Edit','description' => 'For HR administrators only'],
                    ['name' => 'HR View','description' => 'For HR view only'],
                
                    ['name' => 'Teacher','description' => 'For Subject Teachers'],
                    ['name' => 'Class Teacher','description' => 'For Attendance, Class Students and Sponsors Access'],
        
                    #Attendance roles
                    ['name' => 'Attendance Admin','description' => 'For Class Teachers and Administrators'],
                    ['name' => 'Attendance View','description' => 'For Attendance Viewing only'],
        
                    #Assessment roles
                    ['name' => 'Assessment Admin','description' => 'For HODs and Admin users only'],
                    ['name' => 'Assessment Edit','description' => 'For HODs and Admin users only'],
                    ['name' => 'Assessment View','description' => 'For viewing assessment only'],
        
                    #Academics roles
                    ['name' => 'Academic Admin','description' => 'For Academic module access only'],
                    ['name' => 'Academic Edit','description' => 'For Editing academics only'],
                    ['name' => 'Academic View','description' => 'For Viewing academics only'],

                    ['name' => 'HOD','description' => 'Head of departments access to academics'],

                    #Fees Administration roles
                    ['name' => 'Fees & Charges Admin','description' => 'For Admin access to the fees & charges module'],
                    ['name' => 'Fees & Charges Edit','description' => 'For Editing access to the fees & charges module'],
                    ['name' => 'Fees & Chargges View','description' => 'For View access to the fees & charges module'],

                    #Communications roles
                    ['name' => 'Communications Admin','description' => 'For Admin access to notifications'],
                    ['name' => 'Communications Edit','description' => 'For Editing access to notifications'],
                    ['name' => 'Communications View','description' => 'For View access to notifications'],

                    ['name' => 'SMS Admin','description' => 'For Admin access to sending messages'],
                    ['name' => 'Bulk Report Cards','description' => 'For sending bulk report cards'],
        
                    #Houses roles
                    ['name' => 'Houses Admin','description' => 'For Houses module Admin access'],
                    ['name' => 'Houses Edit','description' => 'For Editing Houses'],
                    ['name' => 'Houses View','description' => 'For Viewing houses only'],

                    #Asset Management roles
                    ['name' => 'Asset Management Admin','description' => 'For Asset Management module Admin access'],
                    ['name' => 'Asset Management Edit','description' => 'For Editing Assets'],
                    ['name' => 'Asset Management View','description' => 'For Viewing assets only'],

                    #Welfare roles
                    ['name' => 'School Counsellor','description' => 'School counselor with access to confidential student welfare records'],
                    ['name' => 'Welfare Admin','description' => 'Full access to all welfare module features'],
                    ['name' => 'Welfare View','description' => 'View-only access to welfare records (respects confidentiality levels)'],
                    ['name' => 'Nurse','description' => 'School nurse with access to health incidents'],

                    #System Setup
                    ['name' => 'System Setup','description' => 'For Administrators only'],
                    ['name' => 'Data Importing','description' => 'For data importing users only'],

                ]
            );
        }catch (\Exception $e) {
            Log::error("Failed to insert roles: " . $e->getMessage());
        }
    }


    public function down(){
        Schema::dropIfExists('roles');
    }
};
