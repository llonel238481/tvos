<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Travel_Lists;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {        
        $user = Auth::user();
        $role = $user->role ?? 'Employee';

        // ✅ Common travel order stats
        $totalTravelOrders = Travel_Lists::count();
        $pendingTravelOrders = Travel_Lists::where('status', 'Pending')->count();
        $approvedTravelOrders = Travel_Lists::where('status', 'Approved')->count();
        $cancelledTravelOrders = Travel_Lists::where('status', 'Cancelled')->count();

        // ✅ Monthly counts for chart
        $monthlyCounts = Travel_Lists::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill missing months with 0
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

        // ✅ Compact variables to share with views
        $sharedData = compact(
            'totalTravelOrders',
            'pendingTravelOrders',
            'approvedTravelOrders',
            'cancelledTravelOrders',
            'janOrders', 'febOrders', 'marOrders', 'aprOrders', 'mayOrders', 'junOrders',
            'julOrders', 'augOrders', 'sepOrders', 'octOrders', 'novOrders', 'decOrders'
        );

        // ✅ Switch dashboard by role
        switch ($role) {
            case 'Admin':
                return view('dashboards.dashboard-admin', $sharedData);

            case 'CEO':
                return view('dashboards.dashboard-ceo', $sharedData);

            case 'Supervisor':
                return view('dashboards.dashboard-supervisor', $sharedData);

            default: // Employee
                return view('dashboards.dashboard-employee', $sharedData);
        }
    }
}
