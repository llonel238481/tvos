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
   public function index(Request $request)
    {
        $query = Travel_Lists::with(['transportation', 'requestParties', 'faculty', 'ceo', 'employee.department']);

        if (Auth::check()) {
        $user = Auth::user();

            if ($user->role === 'CEO') {
                // ✅ CEO sees all travel lists
                // No where() filter needed for CEO
                $query = Travel_Lists::with(['transportation', 'requestParties', 'faculty', 'ceo', 'employee.department']);

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
                    $query->whereNull('id'); // no department, no results
                }

            } elseif ($user->role === 'Employee') {
                $employee = Employees::where('user_id', $user->id)->first();
                $query->when($employee, fn($q) => $q->where('employee_id', $employee->id))
                    ->when(!$employee, fn($q) => $q->whereNull('employee_id'));
            }
        }


        // 🔍 Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                ->orWhereHas('requestParties', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        // Optional: filter by status if passed
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $travel_lists = $query->latest()->get();
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
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found for this user.');
        }

        // 🧑‍🏫 Auto-assign faculty (supervisor) based on department
        $faculty = Faculty::where('department_id', $employee->department_id)->first();
        if (!$faculty) {
            return redirect()->back()->with('error', 'No supervisor found for your department.');
        }

        // 🧑‍💼 Auto-assign current CEO
        // You can define "current" CEO by a column like `is_active = true` or just pick the latest one
        $ceo = CEO::where('is_active', true)->first() ?? CEO::latest()->first();
        if (!$ceo) {
            return redirect()->back()->with('error', 'No CEO record found.');
        }

        $travel = Travel_Lists::create([
            'travel_from' => $validated['travel_from'],
            'travel_to' => $validated['travel_to'],
            'purpose' => $validated['purpose'],
            'destination' => $validated['destination'],
            'conditionalities' => null,
            'transportation_id' => $validated['transportation_id'],
            'faculty_id' => $faculty->id, // ✅ auto set supervisor
            'ceo_id' => $ceo->id,         // ✅ auto set CEO
            'employee_id' => $employee->id,
            'status' => 'Pending',
        ]);

        // 👥 Add request parties
        $parties = json_decode($request->request_parties, true) ?? [];
        foreach ($parties as $name) {
            if (!empty(trim($name))) {
                TravelRequestParty::create([
                    'travel_list_id' => $travel->id,
                    'name' => trim($name),
                ]);
            }
        }

        // 🔔 Notify Supervisor
        if ($faculty->user_id) {
            Notification::create([
                'title' => 'New Travel Order Submitted',
                'message' => 'A new travel order has been submitted and needs your approval.',
                'user_id' => $faculty->user_id,
                'travel_id' => $travel->id,
            ]);
        }

        return redirect()->route('travellist.index')->with('success', 'Travel list created successfully. Supervisor and CEO assigned automatically.');
    }



    public function supervisorApprove($id)
    {
        $travel = Travel_Lists::findOrFail($id);

        if ($travel->status !== 'Pending') {
            return redirect()->back()->with('error', 'This travel order cannot be approved.');
        }

        $faculty = Faculty::where('user_id', Auth::id())->first();
        if (!$faculty || !$faculty->signature) {
            return redirect()->back()->with('error', 'No signature found for this supervisor.');
        }

        $travel->update([
            'status' => 'Recommended for Approval',
            'supervisor_signature' => $faculty->signature,
        ]);

        // 🗑️ Delete related Supervisor notification
        Notification::where('travel_id', $travel->id)
            ->where('user_id', Auth::id())
            ->delete();

        // 🔔 Notify CEO
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

    public function ceoApprove(Request $request, $id)
    {
        $travel = Travel_Lists::findOrFail($id);

        if ($travel->status !== 'Recommended for Approval') {
            return redirect()->back()->with('error', 'This travel order cannot be approved at its current status.');
        }

        $request->validate([
            'conditionalities' => 'required|in:On Official Business,On Official Time,On Official Business and Time',
        ]);

        $ceo = $travel->ceo;
        if (!$ceo || !$ceo->signature) {
            return redirect()->back()->with('error', 'The assigned CEO does not have a signature.');
        }

        $travel->update([
            'status' => 'CEO Approved',
            'conditionalities' => $request->conditionalities,
            'ceo_signature' => $ceo->signature,
        ]);

        // 🗑️ Delete CEO Notification for this travel
        Notification::where('travel_id', $travel->id)
            ->where('user_id', Auth::id())
            ->delete();

        // 🔔 Notify Employee
        $employeeUserId = Employees::find($travel->employee_id)->user_id ?? null;
        if ($employeeUserId) {
            Notification::create([
                'title' => 'Travel Order Approved',
                'message' => 'Your travel order has been approved by the CEO.',
                'user_id' => $employeeUserId,
                'travel_id' => $travel->id,
            ]);
        }

        return redirect()->back()->with('success', 'Travel order successfully approved by CEO.');
    }

    public function destroy($id)
    {
        $travel = Travel_Lists::findOrFail($id);

        // 🧹 Delete all related notifications first
        Notification::where('travel_id', $travel->id)->delete();

        // 🔔 Notify involved users about cancellation
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
