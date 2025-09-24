<?php

namespace App\Http\Controllers;
use App\Models\Employees;
use App\Models\Department;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    public function index()
    {
        $employees = Employees::with('department')->get(); // eager load
        $departments = Department::all(); // fetch departments for dropdowns
        return view('employee.employee', compact('employees','departments'));
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
            
        ]);

        Employees::create($validated);
        return redirect()->route('employee.index')->with('success', 'Employee created successfully.');
    }

    public function edit($id)
    {
        $employee = Employees::findOrFail($id);
        return view('employee.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'extensionname' => 'nullable|string|max:10',
            'department_id' => 'required|exists:departments,id',
            'sex' => 'required|in:Male,Female',
        ]);

        $employee = Employees::findOrFail($id);
        $employee->update($validated);
        return redirect()->route('employee.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        $employee = Employees::findOrFail($id);
        $employee->delete();
        return redirect()->route('employee.index')->with('success', 'Employee deleted successfully.');
    }

   public function getEmployees(Request $request)
    {
        $query = Employees::with('department'); // eager load department

        // 🔎 Search by firstname, lastname, middlename
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', '%' . $search . '%')
                ->orWhere('middlename', 'like', '%' . $search . '%')
                ->orWhere('lastname', 'like', '%' . $search . '%');
            });
        }

        // 🏷️ Filter by classification
        if ($request->filled('classification') && $request->classification !== 'All') {
            $query->where('classification', $request->classification);
        }

        // paginate (better for large data)
        $employees = $query->orderBy('lastname')->paginate(10);

        // ✅ must also return departments
        $departments = Department::all();

        return view('employee.employee', compact('employees', 'departments'));
    }

}