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
                            </a>
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

            <!-- Chart Card -->
            <div class="card bg-base-100 shadow-xl mb-8">
                <div class="card-body">
                    <h2 class="card-title mb-4">Monthly Travel Orders</h2>
                    <div class="w-full h-64 md:h-96">
                        <canvas id="travelChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart.js CDN -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('travelChart').getContext('2d');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: [
                                'January', 'February', 'March', 'April', 'May', 'June',
                                'July', 'August', 'September', 'October', 'November', 'December'
                            ],
                            datasets: [{
                                label: 'Travel Orders',
                                data: [
                                    {{ $janOrders ?? 0 }},
                                    {{ $febOrders ?? 0 }},
                                    {{ $marOrders ?? 0 }},
                                    {{ $aprOrders ?? 0 }},
                                    {{ $mayOrders ?? 0 }},
                                    {{ $junOrders ?? 0 }},
                                    {{ $julOrders ?? 0 }},
                                    {{ $augOrders ?? 0 }},
                                    {{ $sepOrders ?? 0 }},
                                    {{ $octOrders ?? 0 }},
                                    {{ $novOrders ?? 0 }},
                                    {{ $decOrders ?? 0 }}
                                ],
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: true, position: 'top' },
                                tooltip: { enabled: true }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { stepSize: 5 } }
                            }
                        }
                    });
                });
            </script>

            <!-- Recent Activity Table -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Recent Activity</h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Purpose</th>
                                    <th>Destination</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (\App\Models\Travel_Lists::latest()->take(5)->get() as $travel)
                                <tr>
                                    <td>{{ $travel->id }}</td>
                                    <td>{{ $travel->purpose }}</td>
                                    <td>{{ $travel->destination }}</td>
                                    <td>{{ $travel->travel_date }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($travel->status == 'Pending') badge-warning
                                            @elseif($travel->status == 'Approved') badge-success
                                            @elseif($travel->status == 'Cancelled') badge-error
                                            @else badge-neutral
                                            @endif">
                                            {{ $travel->status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach

                                @if(\App\Models\Travel_Lists::count() === 0)
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500">No travel records found</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
