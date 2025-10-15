<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Travel_Lists;
use App\Models\Transportation;
use App\Models\Faculty;
use App\Models\Employees;
use App\Models\TravelRequestParty;
use App\Models\CEO;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class TravelListController extends Controller
{
    // ✅ Show travel list
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get related employee or faculty
        $employee = Employees::with('department')->where('user_id', $user->id)->first();
        $faculty  = Faculty::where('user_id', $user->id)->first();
        $noDepartment = $employee && is_null($employee->department_id);

        // Base query with relationships
        $query = Travel_Lists::with(['transportation', 'requestParties', 'faculty', 'ceo', 'employee.department']);

        switch ($user->role) {
            case 'CEO':
            case 'Admin':
                // See all travel orders
                break;

           case 'Supervisor':
            if ($faculty) {
                $query->where(function ($q) use ($faculty) {
                    // Supervisor's own travel orders (if they have any)
                    $q->where('faculty_id', $faculty->id);

                    // Travel orders of employees in the same department (or no department)
                    $q->orWhereHas('employee', function ($q2) use ($faculty) {
                        if ($faculty->department_id) {
                            $q2->where('department_id', $faculty->department_id);
                        } else {
                            $q2->whereNull('department_id');
                        }
                    });
                });
            } else {
                $query->whereNull('id'); // No faculty record → see nothing
            }
            break;
           
            case 'Employee':
                if ($employee) {
                    $query->where('employee_id', $employee->id); // Own travel orders
                } else {
                    $query->whereNull('id'); // No employee record → see nothing
                }
                break;

            default:
                $query->whereNull('id'); // All others see nothing
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                ->orWhereHas('requestParties', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Fetch results with pagination
        $travel_lists = $query->latest()->paginate(5)->appends($request->query());
        $transportations = Transportation::all();
        $ceos = CEO::all();
        $faculties = Faculty::all();

        return view('travellist.travellist', compact(
            'travel_lists',
            'transportations',
            'ceos',
            'noDepartment',
            'employee',
            'faculties'
        ));
    }



    // ✅ Store a new travel list
    public function store(Request $request)
    {
        $validated = $request->validate([
            'travel_from' => 'required|date',
            'travel_to' => 'required|date|after_or_equal:travel_from',
            'request_parties' => 'required|string',
            'purpose' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'transportation_id' => 'required|exists:transportations,id',
            'faculty_id' => 'nullable|exists:faculties,id', // for employees without department
        ]);

        $user = Auth::user();
        $employee = Employees::with('department')->where('user_id', $user->id)->first();
        $faculty  = Faculty::where('user_id', $user->id)->first();

        $faculty_id = null;
        $status = 'Pending';
        $supervisor_signature = null;
        $employee_id = $employee->id ?? null;

        if ($user->role === 'Employee' && $employee) {
            if ($employee->department_id) {
                // Employee with department → auto-assign department faculty
                $facultyRecord = Faculty::where('department_id', $employee->department_id)->first();
                if (!$facultyRecord) return back()->with('error', 'No faculty found for your department.');
                $faculty_id = $facultyRecord->id;
            } else {
                // Employee without department → must select faculty approver
                if (!$request->faculty_id) return back()->with('error', 'Please select a recommending approver.');
                $facultyRecord = Faculty::find($request->faculty_id);
                if (!$facultyRecord) return back()->with('error', 'Selected faculty not found.');
                $faculty_id = $facultyRecord->id;
            }
        } elseif ($user->role === 'Supervisor') {
            // Supervisor creating order → must select a faculty approver
            if (!$request->faculty_id) return back()->with('error', 'Please select a faculty approver.');
            $facultyRecord = Faculty::find($request->faculty_id);
            if (!$facultyRecord) return back()->with('error', 'Selected faculty not found.');

            $faculty_id = $facultyRecord->id;
            $supervisor_signature = $faculty->signature ?? null;
            $status = 'Recommended for Approval';
            $employee_id = null; // no employee assigned
        } elseif ($user->role === 'Faculty') {
            // Faculty creating travel order → assign themselves
            $faculty_id = $faculty->id;
            $employee_id = null;
            $status = 'Pending';
        }

        // Get active CEO
        $ceo = CEO::where('is_active', true)->first() ?? CEO::latest()->first();
        if (!$ceo) return back()->with('error', 'No CEO record found.');

        // Generate travel code
        $year = now()->year;
        $month = now()->format('m');
        $count = Travel_Lists::whereYear('created_at', $year)->count() + 1;
        $code = "OCEO-TO-{$year}-{$month}-" . str_pad($count, 3, '0', STR_PAD_LEFT);

        // Create travel list
        $travel = Travel_Lists::create([
            'travel_code' => $code,
            'travel_from' => $validated['travel_from'],
            'travel_to' => $validated['travel_to'],
            'purpose' => $validated['purpose'],
            'destination' => $validated['destination'],
            'conditionalities' => null,
            'transportation_id' => $validated['transportation_id'],
            'faculty_id' => $faculty_id,
            'ceo_id' => $ceo->id,
            'employee_id' => $employee_id,
            'status' => $status,
            'supervisor_signature' => $supervisor_signature,
        ]);

        // Add request parties
        $parties = json_decode($request->request_parties, true) ?? [];
        foreach ($parties as $name) {
            $clean = ucwords(strtolower(trim($name)));
            if ($clean) {
                TravelRequestParty::create([
                    'travel_list_id' => $travel->id,
                    'name' => $clean,
                ]);
            }
        }

        // Notify CEO
        if ($ceo && $ceo->user_id) {
            Notification::create([
                'title' => 'New Travel Order Submitted',
                'message' => 'A new travel order requires your approval.',
                'user_id' => $ceo->user_id,
                'travel_id' => $travel->id,
            ]);
        }

        return redirect()->route('travellist.index')->with('success', 'Travel Order created successfully.');
    }






    // ✅ Supervisor/Faculty approval
    public function supervisorApprove($id)
    {
        $travel = Travel_Lists::findOrFail($id);
        if ($travel->status !== 'Pending') return redirect()->back()->with('error', 'This travel order cannot be approved.');

        $faculty = Faculty::where('user_id', Auth::id())->first();
        if (!$faculty || !$faculty->signature) return redirect()->back()->with('error', 'No signature found for this faculty.');

        $travel->update([
            'status' => 'Recommended for Approval',
            'supervisor_signature' => $faculty->signature,
        ]);

        Notification::where('travel_id', $travel->id)->where('user_id', Auth::id())->delete();

        if ($travel->ceo && $travel->ceo->user_id) {
            Notification::create([
                'title' => 'Travel Order Recommended',
                'message' => 'A travel order has been recommended for your approval.',
                'user_id' => $travel->ceo->user_id,
                'travel_id' => $travel->id,
            ]);
        }

        return redirect()->back()->with('success', 'Travel order approved by Faculty.');
    }

    // ✅ CEO approval
    public function ceoApprove(Request $request, $id)
    {
        $travel = Travel_Lists::findOrFail($id);
        if ($travel->status !== 'Recommended for Approval') return redirect()->back()->with('error', 'Cannot approve this travel order now.');

        $request->validate(['conditionalities' => 'required|in:On Official Business,On Official Time,On Official Business and Time']);

        $ceo = $travel->ceo;
        if (!$ceo || !$ceo->signature) return redirect()->back()->with('error', 'CEO signature missing.');

        $travel->update([
            'status' => 'CEO Approved',
            'conditionalities' => $request->conditionalities,
            'ceo_signature' => $ceo->signature,
        ]);

        Notification::where('travel_id', $travel->id)->where('user_id', Auth::id())->delete();

        $employeeUserId = Employees::find($travel->employee_id)->user_id ?? null;
        if ($employeeUserId) {
            Notification::create([
                'title' => 'Travel Order Approved',
                'message' => 'Your travel order has been approved by the CEO.',
                'user_id' => $employeeUserId,
                'travel_id' => $travel->id,
            ]);
        }

        return redirect()->back()->with('success', 'Travel order approved by CEO.');
    }

    // ✅ Travel history
    public function history()
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'Employee':
                $employee = Employees::where('user_id', $user->id)->first();
                $travelHistory = $employee
                    ? Travel_Lists::where('employee_id', $employee->id)->orderBy('created_at', 'desc')->get()
                    : collect();
                break;

            case 'Supervisor':
                $faculty = Faculty::where('user_id', $user->id)->first();
                $travelHistory = ($faculty && $faculty->department_id)
                    ? Travel_Lists::whereHas('employee', fn($q) => $q->where('department_id', $faculty->department_id))
                        ->orderBy('created_at', 'desc')->get()
                    : collect();
                break;

            case 'CEO':
            case 'Admin':
                $travelHistory = Travel_Lists::orderBy('created_at', 'desc')->get();
                break;

            default:
                return redirect()->back()->with('error', 'Unauthorized.');
        }

        return view('travellist.history', compact('travelHistory'));
    }

    // ✅ Cancel travel
    public function cancel(Request $request, $id)
    {
        $travel = Travel_Lists::findOrFail($id);

        if (Auth::user()->role !== 'CEO') return redirect()->back()->with('error', 'Unauthorized action.');

        $validated = $request->validate(['reason' => 'required|string|max:1000']);

        if (!in_array($travel->status, ['Pending', 'Recommended for Approval', 'CEO Approved'])) {
            return redirect()->back()->with('error', 'This travel order cannot be cancelled.');
        }

        $travel->update(['status' => 'Cancelled', 'cancellation_reason' => $validated['reason']]);
        Notification::where('travel_id', $travel->id)->delete();

        // Notify employee and faculty
        $employeeUserId = $travel->employee->user_id ?? null;
        $facultyUserId = $travel->faculty->user_id ?? null;

        if ($employeeUserId) {
            Notification::create([
                'title' => 'Travel Order Cancelled',
                'message' => 'Your travel order has been cancelled. Reason: ' . $validated['reason'],
                'user_id' => $employeeUserId,
                'travel_id' => $travel->id,
            ]);
        }

        if ($facultyUserId) {
            Notification::create([
                'title' => 'Travel Order Cancelled',
                'message' => 'A travel order under your supervision has been cancelled. Reason: ' . $validated['reason'],
                'user_id' => $facultyUserId,
                'travel_id' => $travel->id,
            ]);
        }

        return redirect()->back()->with('success', 'Travel order cancelled successfully.');
    }

    // ✅ Delete travel
    public function destroy($id)
    {
        $travel = Travel_Lists::findOrFail($id);
        Notification::where('travel_id', $travel->id)->delete();

        $employeeUserId = $travel->employee->user_id ?? null;
        $facultyUserId = $travel->faculty->user_id ?? null;
        $ceoUserId = $travel->ceo->user_id ?? null;

        foreach ([$employeeUserId, $facultyUserId, $ceoUserId] as $userId) {
            if ($userId) {
                Notification::create([
                    'title' => 'Travel Order Cancelled',
                    'message' => 'This travel order has been cancelled.',
                    'user_id' => $userId,
                    'travel_id' => $travel->id,
                ]);
            }
        }

        $travel->delete();

        return redirect()->route('travellist.index')->with('success', 'Travel list deleted successfully.');
    }
}
