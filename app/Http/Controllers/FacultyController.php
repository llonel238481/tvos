<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::all();
        return view('faculty.faculty', compact('faculties'));
    }

    public function show($id)
    {
        $faculty = Faculty::findOrFail($id);
        return view('faculty.show', compact('faculty'));
    }

    public function create()
    {
        return view('faculty.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facultyname' => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'department'  => 'required|in:CICS,CIT,CTED',
            'contact'     => 'required|string|max:11',
        ]);

        Faculty::create($validated);
        return redirect()->route('faculties.index')->with('success', 'Faculty member created successfully.');
    }

    public function edit($id)
    {
        $faculty = Faculty::findOrFail($id);
        return view('faculty.edit', compact('faculty'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'facultyname' => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'department'  => 'required|in:CICS,CIT,CTED',
            'contact'     => 'required|string|max:11',
        ]);

        $faculty = Faculty::findOrFail($id);
        $faculty->update($validated);
        return redirect()->route('faculties.index')->with('success', 'Faculty member updated successfully.');
    }

    public function destroy($id)
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->delete();
        return redirect()->route('faculties.index')->with('success', 'Faculty member deleted successfully.');
    }
}
