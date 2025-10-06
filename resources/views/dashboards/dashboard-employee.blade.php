<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        <!-- Top Section -->
        <div class="flex flex-col md:flex-row justify-between md:items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Employee Dashboard</h1>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="stats bg-base-100 shadow-sm border border-gray-100">
                <div class="stat">
                    <div class="stat-title text-gray-500">My Travel Orders</div>
                    <div class="stat-value text-primary">{{ $totalTravelOrders }}</div>
                    <div class="stat-desc text-gray-400">{{ now()->format('F d, Y') }}</div>
                </div>
            </div>

            <div class="stats bg-base-100 shadow-sm border border-gray-100">
                <div class="stat">
                    <div class="stat-title text-gray-500">Pending Orders</div>
                    <div class="stat-value text-yellow-500">{{ $pendingTravelOrders }}</div>
                    <div class="stat-desc text-gray-400">Awaiting approval</div>
                </div>
            </div>

            <div class="stats bg-base-100 shadow-sm border border-gray-100">
                <div class="stat">
                    <div class="stat-title text-gray-500">Approved Orders</div>
                    <div class="stat-value text-green-600">{{ $approvedTravelOrders }}</div>
                    <div class="stat-desc text-gray-400">Approved requests</div>
                </div>
            </div>
        </div>

        <!-- Alerts + Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Alerts Card -->
            <div class="card bg-base-100 border border-gray-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg font-semibold text-gray-800">Travel Alerts</h2>
                    <div class="space-y-2">
                        @if($pendingTravelOrders > 0)
                            <div class="alert bg-yellow-50 text-yellow-700 border border-yellow-200">
                                <span>{{ $pendingTravelOrders }} travel orders are pending approval.</span>
                            </div>
                        @endif

                        @if($approvedTravelOrders > 0)
                            <div class="alert bg-green-50 text-green-700 border border-green-200">
                                <span>{{ $approvedTravelOrders }} travel orders have been approved.</span>
                            </div>
                        @endif

                        @if($pendingTravelOrders == 0 && $approvedTravelOrders == 0)
                            <div class="alert bg-gray-50 text-gray-600 border border-gray-200">
                                <span>No travel alerts at the moment 🎉</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card bg-base-100 border border-gray-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg font-semibold text-gray-800">Quick Actions</h2>
                    <a href="{{ route('travellist.index') }}" class="btn btn-primary w-full">View Travel Orders</a>
                </div>
            </div>
        </div>

        <!-- Monthly Chart -->
        <div class="card bg-base-100 border border-gray-100 shadow-sm mb-8">
            <div class="card-body">
                <h2 class="card-title text-lg font-semibold text-gray-800">Monthly Travel Orders</h2>
                <div class="relative w-full h-64">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card bg-base-100 border border-gray-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg font-semibold text-gray-800 mb-3">Recent Activity</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr class="text-gray-500">
                                <th>#</th>
                                <th>Purpose</th>
                                <th>Destination</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (\App\Models\Travel_Lists::latest()->take(5)->get() as $travel)
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
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-400 py-4">No travel records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Travel Orders',
                    data: [
                        {{ $janOrders }}, {{ $febOrders }}, {{ $marOrders }}, {{ $aprOrders }},
                        {{ $mayOrders }}, {{ $junOrders }}, {{ $julOrders }}, {{ $augOrders }},
                        {{ $sepOrders }}, {{ $octOrders }}, {{ $novOrders }}, {{ $decOrders }}
                    ],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)', // primary blue
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</x-app-layout>
