<?php

namespace App\Http\Controllers;

use App\Models\CreateTest;
use Illuminate\Http\Request;

class CreateTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('createtest');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CreateTest $createtest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CreateTest $createtest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CreateTest $createtest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CreateTest $createtest)
    {
        //
    }
}
