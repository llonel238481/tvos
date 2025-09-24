<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Travel_Lists;
use Illuminate\Support\Facades\Auth;

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
        $completedTravelOrders = Travel_Lists::where('status', 'Completed')->count();
        $cancelledTravelOrders = Travel_Lists::where('status', 'Cancelled')->count();

        // ✅ Switch by role
        switch ($role) {
            case 'Admin':
                return view('dashboards.dashboard-admin', compact(
                    'totalTravelOrders',
                    'pendingTravelOrders',
                    'approvedTravelOrders',
                    'completedTravelOrders',
                    'cancelledTravelOrders'
                ));

            case 'CEO':
                return view('dashboards.dashboard-ceo', compact(
                    'totalTravelOrders',
                    'approvedTravelOrders',
                    'completedTravelOrders'
                ));

            case 'Supervisor':
                return view('dashboards.dashboard-supervisor', compact(
                    'totalTravelOrders',
                    'pendingTravelOrders',
                    'approvedTravelOrders'
                ));

            default: // Employee
                // Example: show only the employee’s own requests
                 $myTravelOrders = Travel_Lists::where('request', $user->name)->count();
                    $pendingTravelOrders = Travel_Lists::where('request', $user->name)
                                                        ->where('status', 'Pending')
                                                        ->count();
                    $approvedTravelOrders = Travel_Lists::where('request', $user->name)
                                                        ->where('status', 'Approved')
                                                        ->count();

                    return view('dashboards.dashboard-employee', compact(
                        'myTravelOrders',
                        'pendingTravelOrders',
                        'approvedTravelOrders',
                    ));
        }
    }
}
