<?php

namespace App\Http\Controllers;

use App\Models\Employees;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeesController extends Controller
{
    public function index(Request $request)
    {
        $query = Employees::with('department');

        // Optional: add search & filter if you want index() to behave like getEmployees()
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                ->orWhere('middlename', 'like', "%{$search}%")
                ->orWhere('lastname', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department') && $request->department !== 'All') {
            $query->whereHas('department', function ($q) use ($request) {
                $q->where('departmentname', $request->department);
            });
        }

        // ✅ use paginate() instead of get()
        $employees = $query->orderBy('lastname')->paginate(10);
        $departments = Department::all();

        return view('employee.employee', compact('employees', 'departments'));
    }


    public function show($id)
    {
        $employee = Employees::findOrFail($id);
        return view('employee.show', compact('employee'));
    }

    public function create()
    {
        return view('employee.create');
    }

   public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'extensionname' => 'nullable|string|max:10',
            'department_id' => 'required|exists:departments,id',
            'sex' => 'required|in:Male,Female',
            'email' => 'required|email|unique:users,email', // ✅
        ]);

        // 🧠 Generate full name
        $middleInitial = !empty($validated['middlename']) ? strtoupper($validated['middlename'][0]) . '.' : '';
        $fullName = "{$validated['firstname']} {$middleInitial} {$validated['lastname']}";

        // 🧠 Create linked User
        $user = User::create([
            'name' => $fullName,
            'email' => $validated['email'],
            'role' => 'Employee', // default role
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(60),
        ]);

        // 🧠 Create the employee record & link user_id
        $validated['user_id'] = $user->id;
        Employees::create($validated);

        return redirect()->route('employee.index')->with('success', 'Employee created successfully.');
    }


    public function edit($id)
    {
        $employee = Employees::findOrFail($id);
        $departments = Department::all(); // ✅ Added for dropdown
        return view('employee.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employees::findOrFail($id);

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'extensionname' => 'nullable|string|max:10',
            'department_id' => 'required|exists:departments,id',
            'sex' => 'required|in:Male,Female',
            'email' => 'required|email|unique:users,email,' . $employee->user_id, // ✅ use user_id for exception
        ]);

        $employee->update($validated);

        // 🧠 Update linked User
        if ($employee->user_id) {
            $user = User::find($employee->user_id);
            if ($user) {
                $fullName = "{$validated['firstname']} {$validated['middlename']} {$validated['lastname']}";
                $user->update([
                    'name' => $fullName,
                    'email' => $validated['email'],
                ]);
            }
        }

        return redirect()->route('employee.index')->with('success', 'Employee updated successfully.');
    }


    public function destroy($id)
    {
        $employee = Employees::findOrFail($id);

        if ($employee->user_id) {
            User::where('id', $employee->user_id)->delete();
        }

        $employee->delete();

        return redirect()->route('employee.index')->with('success', 'Employee and linked user deleted successfully.');
    }

  



}
