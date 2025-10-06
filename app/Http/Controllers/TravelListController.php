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
        $query = Travel_Lists::with(['transportation', 'requestParties', 'faculty', 'ceo']);

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'CEO') {
                $query->where('status', 'Recommended for Approval');
            } elseif ($user->role === 'Supervisor') {
                $query->whereIn('status', ['Pending', 'Recommended for Approval']);
            } elseif ($user->role === 'Employee') {
                $employee = Employees::where('user_id', $user->id)->first();
                if ($employee) {
                    $query->where('employee_id', $employee->id);
                } else {
                    $query->whereNull('employee_id');
                }
            } else {
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('requestParties', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                ->orWhere('purpose', 'like', "%{$search}%");
            });
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
            'faculty_id' => 'required|exists:faculties,id',
            'ceo_id' => 'nullable|exists:c_e_o_s,id',
        ]);

        $employee = Employees::where('user_id', Auth::id())->first();
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found for this user.');
        }

        $travel = Travel_Lists::create([
            'travel_from' => $validated['travel_from'],
            'travel_to' => $validated['travel_to'],
            'purpose' => $validated['purpose'],
            'destination' => $validated['destination'],
            'conditionalities' => null,
            'transportation_id' => $validated['transportation_id'],
            'faculty_id' => $validated['faculty_id'],
            'ceo_id' => $validated['ceo_id'] ?? null,
            'employee_id' => $employee->id,
            'status' => 'Pending',
        ]);

        $names = preg_split('/\r\n|\r|\n/', trim($validated['request_parties']));
        foreach ($names as $name) {
            if (!empty(trim($name))) {
                TravelRequestParty::create([
                    'travel_list_id' => $travel->id,
                    'name' => trim($name),
                ]);
            }
        }

        // 🔔 Notify Supervisor
        $faculty = Faculty::find($validated['faculty_id']);
        if ($faculty && $faculty->user_id) {
            Notification::create([
                'title' => 'New Travel Order Submitted',
                'message' => 'A new travel order has been submitted and needs your approval.',
                'user_id' => $faculty->user_id,
                'travel_id' => $travel->id,  // ✅ track
            ]);
        }

        return redirect()->route('travellist.index')->with('success', 'Travel list created successfully.');
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
