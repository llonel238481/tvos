<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Travel_Lists;
use App\Models\Employees;
use App\Models\Faculty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role ?? 'Employee';

        // 📌 Monthly counts for chart (all records)
        $monthlyCounts = Travel_Lists::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $janOrders = $monthlyCounts[1] ?? 0;
        $febOrders = $monthlyCounts[2] ?? 0;
        $marOrders = $monthlyCounts[3] ?? 0;
        $aprOrders = $monthlyCounts[4] ?? 0;
        $mayOrders = $monthlyCounts[5] ?? 0;
        $junOrders = $monthlyCounts[6] ?? 0;
        $julOrders = $monthlyCounts[7] ?? 0;
        $augOrders = $monthlyCounts[8] ?? 0;
        $sepOrders = $monthlyCounts[9] ?? 0;
        $octOrders = $monthlyCounts[10] ?? 0;
        $novOrders = $monthlyCounts[11] ?? 0;
        $decOrders = $monthlyCounts[12] ?? 0;

        // 📝 Default counts
        $totalTravelOrders = Travel_Lists::count();
        $myTravelOrders = 0;
        $deptTravelOrders = 0;
        $pendingTravelOrders = 0;
        $approvedTravelOrders = 0;
        $cancelledTravelOrders = 0;

        // ✅ Role-specific counts
        if ($role === 'Employee') {
            $employee = Employees::where('user_id', $user->id)->first();
            if ($employee) {
                $myTravelOrders = Travel_Lists::where('employee_id', $employee->id)->count();
                $pendingTravelOrders = Travel_Lists::where('employee_id', $employee->id)->where('status', 'Pending')->count();
                $approvedTravelOrders = Travel_Lists::where('employee_id', $employee->id)->where('status', 'Approved')->count();
                $cancelledTravelOrders = Travel_Lists::where('employee_id', $employee->id)->where('status', 'Cancelled')->count();

                $totalTravelOrders = $myTravelOrders;
            }
        } elseif ($role === 'Supervisor') {
            $faculty = Faculty::where('user_id', $user->id)->first();

            if ($faculty && $faculty->department_id) {
                $employeeIds = Employees::where('department_id', $faculty->department_id)->pluck('id');

                $deptTravelOrders = Travel_Lists::whereIn('employee_id', $employeeIds)->count();
                $pendingTravelOrders = Travel_Lists::whereIn('employee_id', $employeeIds)->where('status', 'Pending')->count();
                $approvedTravelOrders = Travel_Lists::whereIn('employee_id', $employeeIds)->where('status', 'Approved')->count();
                $cancelledTravelOrders = Travel_Lists::whereIn('employee_id', $employeeIds)->where('status', 'Cancelled')->count();

                $totalTravelOrders = $deptTravelOrders;
            }
        } else {
            // Admin & CEO see all travel orders
            $pendingTravelOrders = Travel_Lists::where('status', 'Pending')->count();
            $approvedTravelOrders = Travel_Lists::where('status', 'Approved')->count();
            $cancelledTravelOrders = Travel_Lists::where('status', 'Cancelled')->count();
        }

        $sharedData = compact(
            'totalTravelOrders',
            'myTravelOrders',
            'deptTravelOrders',
            'pendingTravelOrders',
            'approvedTravelOrders',
            'cancelledTravelOrders',
            'janOrders','febOrders','marOrders','aprOrders','mayOrders','junOrders',
            'julOrders','augOrders','sepOrders','octOrders','novOrders','decOrders'
        );

        // Switch dashboard by role
        switch ($role) {
            case 'Admin':
                return view('dashboards.dashboard-admin', $sharedData);
            case 'CEO':
                return view('dashboards.dashboard-ceo', $sharedData);
            case 'Supervisor':
                return view('dashboards.dashboard-supervisor', $sharedData);
            default:
                return view('dashboards.dashboard-employee', $sharedData);
        }
    }
}
