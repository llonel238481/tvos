<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Title -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold">CEO Dashboard</h1>
                <p class="text-gray-500 dark:text-gray-400">Overview of travel orders and activities</p>
            </div>

            <!-- 📊 Stats + 📝 Recent Activity -->
            <div class="card bg-base-100 shadow-xl border border-base-300 mb-8">
                <div class="card-body space-y-8">

                    <!-- 🔸 Stats Section -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="stats shadow-lg bg-base-100">
                            <div class="stat">
                                <div class="stat-title">Total Travel Orders</div>
                                <div class="stat-value text-primary">{{ $totalTravelOrders }}</div>
                                <div class="stat-desc">All Departments</div>
                            </div>
                        </div>

                        <div class="stats shadow-lg bg-base-100">
                            <div class="stat">
                                <div class="stat-title">Pending</div>
                                <div class="stat-value text-warning">{{ $pendingTravelOrders }}</div>
                                <div class="stat-desc">Awaiting approval</div>
                            </div>
                        </div>

                        <div class="stats shadow-lg bg-base-100">
                            <div class="stat">
                                <div class="stat-title">Approved</div>
                                <div class="stat-value text-success">{{ $approvedTravelOrders }}</div>
                                <div class="stat-desc">Approved requests</div>
                            </div>
                        </div>

                        <div class="stats shadow-lg bg-base-100">
                            <div class="stat">
                                <div class="stat-title">Cancelled</div>
                                <div class="stat-value text-error">{{ $cancelledTravelOrders }}</div>
                                <div class="stat-desc">Cancelled requests</div>
                            </div>
                        </div>

                    </div>

                    <!-- 📝 Recent Activity Timeline -->
                    <div>
                        <h2 class="font-semibold text-lg mb-4 text-base-content">Recent Activity</h2>
                        <div class="space-y-5">
                            @php
                                $recentTravels = \App\Models\Travel_Lists::latest()->take(3)->get();
                            @endphp

                            @forelse ($recentTravels as $travel)
                                <div class="flex items-start space-x-4">
                                    <!-- Timeline Dot -->
                                    <div class="w-3 h-3 rounded-full mt-2
                                        @if($travel->status == 'Pending') bg-warning
                                        @elseif($travel->status == 'Approved') bg-success
                                        @elseif($travel->status == 'Cancelled') bg-error
                                        @else bg-neutral @endif">
                                    </div>

                                    <!-- Activity Content -->
                                    <div class="flex-1">
                                        <div class="flex justify-between items-center flex-wrap gap-2">
                                            <p class="font-medium text-base-content">{{ $travel->purpose }}</p>
                                            <span class="text-xs text-base-content/70">
                                                {{ \Carbon\Carbon::parse($travel->travel_date)->format('M d, Y') }}
                                            </span>
                                        </div>

                                        <p class="text-sm text-base-content/80">
                                            Destination: <span class="font-semibold">{{ $travel->destination }}</span>
                                        </p>

                                        <!-- Status Badge -->
                                        <div class="mt-2">
                                            @if($travel->status == 'Pending')
                                                <span class="badge badge-warning badge-sm">{{ $travel->status }}</span>
                                            @elseif($travel->status == 'Approved')
                                                <span class="badge badge-success badge-sm">{{ $travel->status }}</span>
                                            @elseif($travel->status == 'Cancelled')
                                                <span class="badge badge-error badge-sm">{{ $travel->status }}</span>
                                            @else
                                                <span class="badge badge-neutral badge-sm">{{ $travel->status }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-base-content/70 py-6">
                                    No recent travel activity found
                                </div>
                            @endforelse
                        </div>

                        <!-- View All Link -->
                        @if(\App\Models\Travel_Lists::count() > 3)
                            <div class="mt-4 text-right">
                                <a href="{{ route('travellist.index') }}" class="text-sm text-primary hover:underline">
                                    View All Recent Travel Orders
                                </a>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
            <!-- 📈 Chart + ⚡ Quick Actions Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chart (Left, spans 2 cols) -->
                <div class="card bg-base-100 shadow-lg border border-base-300 lg:col-span-2 transition duration-200 hover:shadow-xl">
                    <div class="card-body">
                        <h2 class="font-semibold text-lg mb-4">Monthly Travel Orders</h2>
                        <div id="apexChart" class="w-full h-64 md:h-80"></div>
                    </div>
                </div>

                <!-- Quick Actions (Right, fixed width on lg) -->
                <div class="card bg-base-100 shadow-lg border border-base-300 w-full lg:w-80 lg:ml-auto transition duration-200 hover:shadow-xl">
                    <div class="card-body space-y-4">

                        <!-- 🥧 Mini Pie Chart -->
                        <div class="bg-base-200 rounded-lg p-3 shadow-inner">
                            <h2 class="font-semibold text-lg mb-2">Travel Summary</h2>
                            <div id="quickPieChart" class="w-full h-48"></div>
                        </div>

                        <div class="divider my-0"></div>

                        <!-- Quick Action Buttons -->
                        <div>
                            <h2 class="font-semibold text-lg mb-3">Quick Actions</h2>
                            <div class="grid gap-2">
                                <a href="{{ route('travellist.index') }}" class="btn bg-emerald-600 hover:bg-emerald-700 text-white w-full shadow-md hover:shadow-lg transition">
                                    View All Travel Orders
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ApexCharts -->
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
            <script>
            document.addEventListener('DOMContentLoaded', () => {
                // 📊 Area Chart (Left)
                const options = {
                    chart: {
                        type: 'area',
                        height: 320,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        fontFamily: 'inherit'
                    },
                    series: [{
                        name: 'Travel Orders',
                        data: [
                            {{ $janOrders }}, {{ $febOrders }}, {{ $marOrders }}, {{ $aprOrders }},
                            {{ $mayOrders }}, {{ $junOrders }}, {{ $julOrders }}, {{ $augOrders }},
                            {{ $sepOrders }}, {{ $octOrders }}, {{ $novOrders }}, {{ $decOrders }}
                        ]
                    }],
                    xaxis: {
                        categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                        labels: { style: { colors: '#6b7280' } }
                    },
                    yaxis: {
                        labels: { style: { colors: '#6b7280' } },
                        min: 0
                    },
                    stroke: { curve: 'smooth', width: 3 },
                    colors: ['#10b981'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.4,
                            opacityTo: 0,
                            stops: [0, 90, 100]
                        }
                    },
                    dataLabels: { enabled: false },
                    grid: {
                        borderColor: '#e5e7eb',
                        strokeDashArray: 4
                    },
                    tooltip: { theme: 'light' }
                };

                const chart = new ApexCharts(document.querySelector("#apexChart"), options);
                chart.render();

                // 🥧 Mini Donut Chart (Quick Actions box)
                const quickPieOptions = {
                    chart: {
                        type: 'donut',
                        height: 180,
                    },
                    series: [
                        {{ $pendingTravelOrders }},
                        {{ $approvedTravelOrders }},
                        {{ $cancelledTravelOrders }}
                    ],
                    labels: ['Pending', 'Approved', 'Cancelled'],
                    colors: ['#facc15', '#10b981', '#ef4444'],
                    legend: {
                        position: 'bottom',
                        fontSize: '12px',
                        labels: { colors: '#6b7280' }
                    },
                    tooltip: {
                        y: {
                            formatter: (val) => `${val} orders`
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        color: '#374151',
                                        formatter: () => {{ $totalTravelOrders }}
                                    }
                                }
                            }
                        }
                    }
                };

                const quickPieChart = new ApexCharts(document.querySelector("#quickPieChart"), quickPieOptions);
                quickPieChart.render();
            });
            </script>



        </div>
    </div>
</x-app-layout>
