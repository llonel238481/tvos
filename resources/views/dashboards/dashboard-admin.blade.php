<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div>
                <h1 class="text-3xl font-bold mb-4">Admin Dashboard</h1>
            </div>
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="stats shadow">
                    <div class="stat">
                        <div class="stat-title">Total Travel Orders</div>
                        <div class="stat-value">{{ $totalTravelOrders }}</div>
                        <div class="stat-desc">As of {{ now()->format('F d, Y') }}</div>
                    </div>
                </div>
                
                <div class="stats shadow">
                    <div class="stat">
                        <div class="stat-figure text-error">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="inline-block w-8 h-8 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div class="stat-title">Cancelled Orders</div>
                        <div class="stat-value text-error">{{ $cancelledTravelOrders }}</div>
                        <div class="stat-desc">Requests not approved</div>
                    </div>
                </div>


                <div class="stats shadow">
                    <div class="stat">
                        <div class="stat-figure text-success">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="inline-block w-8 h-8 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="stat-title">Approved Orders</div>
                        <div class="stat-value text-success">{{ $approvedTravelOrders }}</div>
                        <div class="stat-desc">Already approved</div>
                    </div>
                </div>

            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                <!-- Alerts Card -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">Travel Alerts</h2>
                        <div class="space-y-2">
                            @if($pendingTravelOrders > 0)
                                <div class="alert alert-warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        class="stroke-current shrink-0 w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01M12 19a7 7 0 100-14 7 7 0 000 14z"></path>
                                    </svg>
                                    <span>{{ $pendingTravelOrders }} pending travel orders awaiting approval.</span>
                                </div>
                            @endif

                            @if($approvedTravelOrders > 0)
                                <div class="alert alert-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        class="stroke-current shrink-0 h-6 w-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $approvedTravelOrders }} travel orders approved and ready for processing.</span>
                                </div>
                            @endif

                            @if($pendingTravelOrders == 0 && $approvedTravelOrders == 0)
                                <div class="alert alert-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        class="stroke-current shrink-0 h-6 w-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                                    </svg>
                                    <span>No travel alerts at the moment. 🎉</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>


                <!-- Quick Actions Card -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">Quick Actions</h2>
                        
                        <div class="grid gap-3">
                            <a href="{{ route('travellist.index') }}" class="btn btn-success w-full">
                                View All Travel Orders
                            <a href="{{ route('transportation.index') }}" class="btn btn-success w-full">
                                Manage Transportation
                            </a>
                            <a href="{{ route('departments.index') }}" class="btn btn-success w-full">
                                Manage Departments
                            </a>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Table -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Recent Activity</h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <label>
                                            <input type="checkbox" class="checkbox" />
                                        </label>
                                    </th>
                                    <th>Name</th>
                                    <th>Job</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>
                                        <label>
                                            <input type="checkbox" class="checkbox" />
                                        </label>
                                    </th>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar">
                                                <div class="mask mask-squircle w-12 h-12">
                                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Cy" alt="Avatar" />
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold">Cy Ganderton</div>
                                                <div class="text-sm opacity-50">United States</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Quality Control Specialist</td>
                                    <td><div class="badge badge-success gap-2">Active</div></td>
                                    <th>
                                        <button class="btn btn-ghost btn-xs">details</button>
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                        <label>
                                            <input type="checkbox" class="checkbox" />
                                        </label>
                                    </th>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar">
                                                <div class="mask mask-squircle w-12 h-12">
                                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Hart" alt="Avatar" />
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold">Hart Hagerty</div>
                                                <div class="text-sm opacity-50">Canada</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Desktop Support Technician</td>
                                    <td><div class="badge badge-warning gap-2">Pending</div></td>
                                    <th>
                                        <button class="btn btn-ghost btn-xs">details</button>
                                    </th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>