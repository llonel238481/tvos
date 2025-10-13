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
use Carbon\Carbon;

class TravelListController extends Controller
{
    // ✅ Show all travel lists depending on user role
    public function index(Request $request)
    {
        $query = Travel_Lists::with(['transportation', 'requestParties', 'faculty', 'ceo', 'employee.department']);

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'CEO') {
                // CEO sees all
            } elseif ($user->role === 'Supervisor') {
                $faculty = Faculty::where('user_id', $user->id)->first();
                if ($faculty && $faculty->department_id) {
                    $query->where(function ($q) use ($faculty) {
                        $q->where(function ($pendingQ) use ($faculty) {
                            $pendingQ->where('status', 'Pending')
                                ->whereHas('employee', fn($empQ) => $empQ->where('department_id', $faculty->department_id));
                        })
                        ->orWhere(function ($approvedQ) use ($faculty) {
                            $approvedQ->where('status', 'Recommended for Approval')
                                ->where('faculty_id', $faculty->id);
                        });
                    });
                } else {
                    $query->whereNull('id'); // no results
                }
            } elseif ($user->role === 'Employee') {
                $employee = Employees::where('user_id', $user->id)->first();
                $query->when($employee, fn($q) => $q->where('employee_id', $employee->id))
                    ->when(!$employee, fn($q) => $q->whereNull('employee_id'));
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                ->orWhereHas('requestParties', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $travel_lists = $query->latest()->paginate(5)->appends($request->query());

        $transportations = Transportation::all();
        $faculties = Faculty::all();
        $ceos = CEO::all();

        return view('travellist.travellist', compact(
            'travel_lists',
            'transportations',
            'faculties',
            'ceos'
        ));
    }

    public function show($id)
    {
        // Optional: redirect to index or history
        return redirect()->route('travellist.index');
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
        ]);

        $employee = Employees::with('department')->where('user_id', Auth::id())->first();
        if (!$employee) return redirect()->back()->with('error', 'Employee record not found.');

        $faculty = Faculty::where('department_id', $employee->department_id)->first();
        if (!$faculty) return redirect()->back()->with('error', 'No supervisor found for your department.');

        $ceo = CEO::where('is_active', true)->first() ?? CEO::latest()->first();
        if (!$ceo) return redirect()->back()->with('error', 'No CEO record found.');
        
        // 👇 Generate the formatted travel code
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
        $countThisYear = Travel_Lists::whereYear('created_at', $year)->count() + 1;
        $number = str_pad($countThisYear, 3, '0', STR_PAD_LEFT);
        $travel_code = "OCEO-TO-{$year}-{$month}-{$number}";
        
        $travel = Travel_Lists::create([
             'travel_code' => $travel_code,
            'travel_from' => $validated['travel_from'],
            'travel_to' => $validated['travel_to'],
            'purpose' => $validated['purpose'],
            'destination' => $validated['destination'],
            'conditionalities' => null,
            'transportation_id' => $validated['transportation_id'],
            'faculty_id' => $faculty->id,
            'ceo_id' => $ceo->id,
            'employee_id' => $employee->id,
            'status' => 'Pending',
        ]);

        $parties = json_decode($request->request_parties, true) ?? [];
        foreach ($parties as $name) {
            $formattedName = ucwords(strtolower(trim($name)));
            if (!empty($formattedName)) {
                TravelRequestParty::create([
                    'travel_list_id' => $travel->id,
                    'name' => $formattedName,
                ]);
            }
        }

        if ($faculty->user_id) {
            Notification::create([
                'title' => 'New Travel Order Submitted',
                'message' => 'A new travel order has been submitted and needs your approval.',
                'user_id' => $faculty->user_id,
                'travel_id' => $travel->id,
            ]);
        }

        return redirect()->route('travellist.index')
            ->with('success', 'Travel list created successfully. Supervisor and CEO assigned automatically.');
    }

    // ✅ Travel history based on role
    public function history()
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'Employee':
                // Employee sees only their own history
                $employee = Employees::where('user_id', $user->id)->first();
                if (!$employee) {
                    return redirect()->back()->with('error', 'Employee profile not found.');
                }
                $travelHistory = Travel_Lists::where('employee_id', $employee->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                break;

            case 'Supervisor':
                // Supervisor sees travel lists from their department
                $faculty = Faculty::where('user_id', $user->id)->first();
                if ($faculty && $faculty->department_id) {
                    $travelHistory = Travel_Lists::whereHas('employee', function($q) use ($faculty) {
                        $q->where('department_id', $faculty->department_id);
                    })->orderBy('created_at', 'desc')->get();
                } else {
                    $travelHistory = collect(); // empty collection if no department
                }
                break;

            case 'CEO':
            case 'Admin':
                // CEO and Admin see all travel lists
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

        // Notify employee and supervisor
        if ($travel->employee && $travel->employee->user_id) {
            Notification::create([
                'title' => 'Travel Order Cancelled',
                'message' => 'Your travel order has been cancelled. Reason: ' . $validated['reason'],
                'user_id' => $travel->employee->user_id,
                'travel_id' => $travel->id,
            ]);
        }

        if ($travel->faculty && $travel->faculty->user_id) {
            Notification::create([
                'title' => 'Travel Order Cancelled',
                'message' => 'A travel order under your supervision has been cancelled. Reason: ' . $validated['reason'],
                'user_id' => $travel->faculty->user_id,
                'travel_id' => $travel->id,
            ]);
        }

        return redirect()->back()->with('success', 'Travel order cancelled successfully.');
    }

    // ✅ Supervisor approve
    public function supervisorApprove($id)
    {
        $travel = Travel_Lists::findOrFail($id);

        if ($travel->status !== 'Pending') return redirect()->back()->with('error', 'This travel order cannot be approved.');

        $faculty = Faculty::where('user_id', Auth::id())->first();
        if (!$faculty || !$faculty->signature) return redirect()->back()->with('error', 'No signature found for this supervisor.');

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

        return redirect()->back()->with('success', 'Travel order approved by Supervisor.');
    }

    // ✅ CEO approve
    public function ceoApprove(Request $request, $id)
    {
        $travel = Travel_Lists::findOrFail($id);

        if ($travel->status !== 'Recommended for Approval') return redirect()->back()->with('error', 'Cannot approve this travel order now.');

        $request->validate(['conditionalities' => 'required|in:On Official Business,On Official Time,On Official Business and Time']);

        $ceo = $travel->ceo;
        if (!$ceo || !$ceo->signature) return redirect()->back()->with('error', 'The assigned CEO does not have a signature.');

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

    // ✅ Delete travel
    public function destroy($id)
    {
        $travel = Travel_Lists::findOrFail($id);
        Notification::where('travel_id', $travel->id)->delete();

        $employeeUserId = Employees::find($travel->employee_id)->user_id ?? null;
        if ($employeeUserId) {
            Notification::create([
                'title' => 'Travel Order Cancelled',
                'message' => 'Your travel order has been cancelled.',
                'user_id' => $employeeUserId,
                'travel_id' => $travel->id,
            ]);
        }

        if ($travel->faculty && $travel->faculty->user_id) {
            Notification::create([
                'title' => 'Travel Order Cancelled',
                'message' => 'A travel order under your supervision has been cancelled.',
                'user_id' => $travel->faculty->user_id,
                'travel_id' => $travel->id,
            ]);
        }

        if ($travel->ceo && $travel->ceo->user_id) {
            Notification::create([
                'title' => 'Travel Order Cancelled',
                'message' => 'A travel order assigned to you has been cancelled.',
                'user_id' => $travel->ceo->user_id,
                'travel_id' => $travel->id,
            ]);
        }

        $travel->delete();

        return redirect()->route('travellist.index')->with('success', 'Travel list deleted successfully.');
    }
}
