<x-app-layout>
    <div class="py-10 bg-base-200 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-emerald-600">Admin Dashboard</h1>
                <p class="text-sm text-gray-500">Overview of travel order activity</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card bg-base-100 shadow-sm border border-base-300">
                    <div class="card-body">
                        <div class="text-sm text-gray-500">Total Travel Orders</div>
                        <div class="text-3xl font-bold text-emerald-600">{{ $totalTravelOrders }}</div>
                        <div class="text-xs text-gray-400">As of {{ now()->format('F d, Y') }}</div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-300">
                    <div class="card-body">
                        <div class="text-sm text-gray-500">Cancelled Orders</div>
                        <div class="text-3xl font-bold text-red-500">{{ $cancelledTravelOrders }}</div>
                        <div class="text-xs text-gray-400">Not approved</div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-300">
                    <div class="card-body">
                        <div class="text-sm text-gray-500">Approved Orders</div>
                        <div class="text-3xl font-bold text-emerald-600">{{ $approvedTravelOrders }}</div>
                        <div class="text-xs text-gray-400">Already approved</div>
                    </div>
                </div>
            </div>

            <!-- Alerts + Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Alerts -->
                <div class="card bg-base-100 shadow-sm border border-base-300">
                    <div class="card-body space-y-3">
                        <h2 class="font-semibold text-lg">Travel Alerts</h2>
                        @if($pendingTravelOrders > 0)
                            <div class="p-3 rounded-lg bg-yellow-100 text-yellow-800 text-sm">
                                {{ $pendingTravelOrders }} pending travel orders awaiting approval.
                            </div>
                        @endif

                        @if($approvedTravelOrders > 0)
                            <div class="p-3 rounded-lg bg-emerald-100 text-emerald-800 text-sm">
                                {{ $approvedTravelOrders }} travel orders approved and ready for processing.
                            </div>
                        @endif

                        @if($pendingTravelOrders == 0 && $approvedTravelOrders == 0)
                            <div class="p-3 rounded-lg bg-gray-100 text-gray-600 text-sm">
                                No travel alerts at the moment.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card bg-base-100 shadow-sm border border-base-300">
                    <div class="card-body space-y-3">
                        <h2 class="font-semibold text-lg">Quick Actions</h2>
                        <div class="grid gap-2">
                            <a href="{{ route('travellist.index') }}" class="btn bg-emerald-600 hover:bg-emerald-700 text-white w-full">
                                View All Travel Orders
                            </a>
                            <a href="{{ route('transportation.index') }}" class="btn bg-emerald-600 hover:bg-emerald-700 text-white w-full">
                                Manage Transportation
                            </a>
                            <a href="{{ route('departments.index') }}" class="btn bg-emerald-600 hover:bg-emerald-700 text-white w-full">
                                Manage Departments
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Chart -->
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body">
                    <h2 class="font-semibold text-lg mb-4">Monthly Travel Orders</h2>
                    <div class="w-full h-64 md:h-96">
                        <canvas id="travelChart"></canvas>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const ctx = document.getElementById('travelChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: [
                                'January','February','March','April','May','June',
                                'July','August','September','October','November','December'
                            ],
                            datasets: [{
                                label: 'Travel Orders',
                                data: [
                                    {{ $janOrders ?? 0 }}, {{ $febOrders ?? 0 }}, {{ $marOrders ?? 0 }},
                                    {{ $aprOrders ?? 0 }}, {{ $mayOrders ?? 0 }}, {{ $junOrders ?? 0 }},
                                    {{ $julOrders ?? 0 }}, {{ $augOrders ?? 0 }}, {{ $sepOrders ?? 0 }},
                                    {{ $octOrders ?? 0 }}, {{ $novOrders ?? 0 }}, {{ $decOrders ?? 0 }}
                                ],
                                borderColor: '#059669',
                                backgroundColor: 'rgba(5, 150, 105, 0.15)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3,
                                pointRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { stepSize: 5 } }
                            }
                        }
                    });
                });
            </script>

            <!-- Recent Activity -->
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body">
                    <h2 class="font-semibold text-lg mb-4">Recent Activity</h2>
                    <div class="overflow-x-auto">
                        <table class="table text-sm">
                            <thead class="bg-gray-100">
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
                                        <td colspan="5" class="text-center text-gray-500 py-4">No travel records found</td>
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
