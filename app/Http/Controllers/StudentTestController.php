<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentTestRequest;
use App\Http\Requests\UpdateStudentTestRequest;
use App\Models\StudentTest;

class StudentTestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStudentTestRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStudentTestRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StudentTest  $studentTest
     * @return \Illuminate\Http\Response
     */
    public function show(StudentTest $studentTest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StudentTest  $studentTest
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentTest $studentTest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStudentTestRequest  $request
     * @param  \App\Models\StudentTest  $studentTest
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStudentTestRequest $request, StudentTest $studentTest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StudentTest  $studentTest
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentTest $studentTest)
    {
        //
    }
}
