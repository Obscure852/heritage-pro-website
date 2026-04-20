<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentTestsRequest;
use App\Http\Requests\UpdateStudentTestsRequest;
use App\Models\StudentTest;

class StudentTestsController extends Controller
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
     * @param  \App\Http\Requests\StoreStudentTestsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStudentTestsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StudentTests  $studentTests
     * @return \Illuminate\Http\Response
     */
    public function show(StudentTest $studentTest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StudentTests  $studentTests
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentTest $studentTest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStudentTestsRequest  $request
     * @param  \App\Models\StudentTests  $studentTests
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStudentTestsRequest $request, StudentTest $studentTest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StudentTests  $studentTests
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentTest $studentTest)
    {
        //
    }
}
