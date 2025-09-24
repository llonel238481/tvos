<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $search = $request->input('search');

    $departments = Department::when($search, function ($query, $search) {
        return $query->where('departmentname', 'like', "%{$search}%");
    })->get();

    return view('departments.departments', compact('departments'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'departmentname' => 'required|string|max:255',
        ]);

        Department::create([
            'departmentname' => $request->departmentname,
        ]);

        return redirect()->route('departments.index')
                         ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'departmentname' => 'required|string|max:255',
        ]);

        $department->update([
            'departmentname' => $request->departmentname,
        ]);

        return redirect()->route('departments.index')
                         ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('departments.index')
                         ->with('success', 'Department deleted successfully.');
    }
}
