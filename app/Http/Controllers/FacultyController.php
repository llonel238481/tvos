<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Faculty;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FacultyController extends Controller
{
    public function index(Request $request)
    {
        $query = Faculty::query()->with('department');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('facultyname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $faculties = $query->latest()->get();
        $departments = Department::all();

        return view('faculty.faculty', compact('faculties', 'departments'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('faculty.create', compact('departments'));
    }

        public function store(Request $request)
    {
        $validated = $request->validate([
            'facultyname'   => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:faculties,email|unique:users,email',
            'contact'       => 'required|string|max:11',
            'department_id' => 'nullable|exists:departments,id',
            'signature'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // ✅ 1. Create User account automatically
        $user = User::create([
            'name'     => $validated['facultyname'],
            'email'    => $validated['email'],
            'password' => Hash::make('password123'), 
            'role'     => 'Supervisor',          // 👈 Default role
        ]);

        // ✅ 2. Handle signature upload if any
        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $path = $file->store('signatures', 'public');
            $validated['signature'] = $path;
        }

        // ✅ 3. Attach the newly created user_id
        $validated['user_id'] = $user->id;

        // ✅ 4. Create Faculty record
        \App\Models\Faculty::create($validated);

        return redirect()->route('faculties.index')->with('success', 'Faculty and user account created successfully.');
    }


    public function edit($id)
    {
        $faculty = Faculty::findOrFail($id);
        $departments = Department::all();
        return view('faculty.edit', compact('faculty', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $faculty = Faculty::findOrFail($id);

        $validated = $request->validate([
            'facultyname'   => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:faculties,email,' . $faculty->id . '|unique:users,email,' . $faculty->user_id,
            'contact'       => 'required|string|max:11',
            'department_id' => 'nullable|exists:departments,id',
            'signature'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // ✅ Update signature if uploaded
        if ($request->hasFile('signature')) {
            // Delete old signature if exists
            if ($faculty->signature && file_exists(public_path('storage/' . $faculty->signature))) {
                unlink(public_path('storage/' . $faculty->signature));
            }
            $fileName = time() . '_' . $request->file('signature')->getClientOriginalName();
            $path = $request->file('signature')->storeAs('signatures', $fileName, 'public');
            $validated['signature'] = $path;
        }

        // ✅ Update Faculty record
        $faculty->update($validated);

        // ✅ Also update corresponding User info
        if ($faculty->user_id) {
            $user = User::find($faculty->user_id);

            if ($user) {
                $user->update([
                    'name'  => $validated['facultyname'],
                    'email' => $validated['email'],
                ]);
            }
        }

        return redirect()->route('faculties.index')->with('success', 'Faculty and user account updated successfully.');
    }


    public function destroy($id)
    {
        $faculty =Faculty::findOrFail($id);

        // ✅ Delete linked user account if exists
        if ($faculty->user_id) {
            $user = User::find($faculty->user_id);
            if ($user) {
                $user->delete();
            }
        }

        // ✅ Delete signature file if exists
        if ($faculty->signature && file_exists(public_path('storage/' . $faculty->signature))) {
            unlink(public_path('storage/' . $faculty->signature));
        }

        // ✅ Finally delete faculty
        $faculty->delete();

        return redirect()->route('faculties.index')->with('success', 'Faculty and linked user account deleted successfully.');
    }

}
