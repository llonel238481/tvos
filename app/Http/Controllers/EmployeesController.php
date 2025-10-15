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

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('middlename', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%");
            });
        }

        // Department filter including "No Department"
        if ($request->filled('department') && $request->department !== 'All') {
            if ($request->department === 'No Department') {
                $query->whereNull('department_id');
            } else {
                $query->whereHas('department', function ($q) use ($request) {
                    $q->where('departmentname', $request->department);
                });
            }
        }

        // Paginate results
        $employees = $query->orderBy('lastname')->paginate(10)->withQueryString();

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
        $departments = Department::all();
        return view('employee.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'extensionname' => 'nullable|string|max:10',
            'department_id' => 'nullable|exists:departments,id', // allow null
            'sex' => 'required|in:Male,Female',
            'email' => 'required|email|unique:users,email',
        ]);

        $middlename = $validated['middlename'] ?? '';
        $middleInitial = $middlename ? strtoupper($middlename[0]) . '.' : '';
        $fullName = trim("{$validated['firstname']} {$middleInitial} {$validated['lastname']}");

        $user = User::create([
            'name' => $fullName,
            'email' => $validated['email'],
            'role' => 'Employee',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(60),
        ]);

        $validated['user_id'] = $user->id;
        Employees::create($validated);

        return redirect()->route('employee.index')->with('success', 'Employee created successfully.');
    }

    public function edit($id)
    {
        $employee = Employees::findOrFail($id);
        $departments = Department::all();
        return view('employee.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employees::findOrFail($id);

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'extensionname' => 'nullable|string|max:10',
            'department_id' => 'nullable|exists:departments,id', // allow null
            'sex' => 'required|in:Male,Female',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
        ]);

        $employee->update($validated);

        if ($employee->user_id) {
            $user = User::find($employee->user_id);
            if ($user) {
                $middlename = $validated['middlename'] ?? '';
                $middleInitial = $middlename ? strtoupper($middlename[0]) . '.' : '';
                $fullName = trim("{$validated['firstname']} {$middleInitial} {$validated['lastname']}");

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
