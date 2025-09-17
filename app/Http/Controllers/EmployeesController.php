<?php

namespace App\Http\Controllers;
use App\Models\Employees;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    public function index()
    {
        $employees = Employees::all();
         $employees = Employees::orderBy('lastname')->paginate(10);
        return view('employee.employee', compact('employees'));
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
            'classification' => 'required|in:Admin,Employee,CEO,Supervisor',
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
            'classification' => 'required|in:Admin,Employee,CEO,Supervisor',
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
    $query = Employees::query();

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('firstname', 'like', '%' . $search . '%');
            //   ->orWhere('lastname', 'like', '%' . $search . '%');
        });
    }

    $employees = $query->get();

    return view('employee.employee', compact('employees'));
}
}