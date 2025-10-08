<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- 🧭 Heading -->
            <div>
                <h1 class="text-3xl font-bold mb-2">Employee Dashboard</h1>
                <p class="text-base-content/70">Overview of travel orders and activities</p>
            </div>

            <!-- 📊 Stats Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stats shadow-lg bg-base-100">
                    <div class="stat">
                        <div class="stat-title">My Travel Orders</div>
                        <div class="stat-value text-primary">{{ $myTravelOrders }}</div>
                        <div class="stat-desc">As of {{ now()->format('F d, Y') }}</div>
                    </div>
                </div>
                
                <div class="stats shadow-lg bg-base-100">
                    <div class="stat">
                        <div class="stat-title">Pending Orders</div>
                        <div class="stat-value text-warning">{{ $pendingTravelOrders }}</div>
                        <div class="stat-desc">Awaiting approval</div>
                    </div>
                </div>

                <div class="stats shadow-lg bg-base-100">
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
                        <div class="stat-desc">Approved requests</div>
                    </div>
                </div>
            </div>

           <!-- 📝 Recent Activity (Modern Card List Style) -->
            <div class="card bg-base-100 shadow-lg hover:shadow-xl transition">
                <div class="card-body">
                    <h2 class="card-title mb-4">Recent Activity</h2>

                    @php
                        $user = Auth::user();

                        if($user->role === 'Supervisor') {
                            $faculty = \App\Models\Faculty::where('user_id', $user->id)->first();
                            if($faculty && $faculty->department_id) {
                                $recentTravels = \App\Models\Travel_Lists::whereHas('employee', function($q) use ($faculty) {
                                    $q->where('department_id', $faculty->department_id);
                                })->latest()->take(3)->get();

                                $totalTravels = \App\Models\Travel_Lists::whereHas('employee', fn($q) => $q->where('department_id', $faculty->department_id))->count();
                            } else {
                                $recentTravels = collect();
                                $totalTravels = 0;
                            }
                        } elseif($user->role === 'Employee') {
                            $employee = \App\Models\Employees::where('user_id', $user->id)->first();
                            $recentTravels = $employee 
                                ? \App\Models\Travel_Lists::where('employee_id', $employee->id)->latest()->take(3)->get()
                                : collect();

                            $totalTravels = $employee 
                                ? \App\Models\Travel_Lists::where('employee_id', $employee->id)->count()
                                : 0;
                        } else {
                            $recentTravels = \App\Models\Travel_Lists::latest()->take(3)->get();
                            $totalTravels = \App\Models\Travel_Lists::count();
                        }
                    @endphp

                    @forelse ($recentTravels as $travel)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between py-3 border-b border-base-200 last:border-none hover:bg-base-200/40 rounded-lg transition">
                        <!-- Left Content -->
                        <div class="flex items-start gap-3">
                            <!-- Icon -->
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>

                            <!-- Text Info -->
                            <div>
                                <p class="font-semibold text-base-content">{{ $travel->purpose }}</p>
                                <p class="text-sm text-base-content/60">{{ $travel->destination }}</p>
                            </div>
                        </div>

                        <!-- Right Content -->
                        <div class="mt-2 sm:mt-0 flex flex-col sm:items-end gap-1 text-sm">
                            <span class="text-base-content/70">
                                {{ \Carbon\Carbon::parse($travel->travel_date)->format('M d, Y') }}
                            </span>
                            <span class="badge 
                                @if($travel->status == 'Pending') badge-warning
                                @elseif($travel->status == 'Recommended for Approval') badge-info
                                @elseif($travel->status == 'CEO Approved') badge-success
                                @elseif($travel->status == 'Cancelled') badge-error
                                @else badge-neutral
                                @endif">
                                {{ $travel->status }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-base-content/60 py-4">
                        No recent travel activity found ✨
                    </div>
                    @endforelse

                    <!-- View All Link -->
                    @if($totalTravels > 3)
                        <div class="text-center mt-2">
                            <a href="{{ route('travellist.index') }}" class="text-blue-600 hover:underline text-sm">
                                View All
                            </a>
                        </div>
                    @endif
                </div>
            </div>


           <!-- 📊 Chart + ⚡ Quick Actions Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 📈 Modern Area Chart (Left) -->
                <div class="card bg-base-100 shadow-lg hover:shadow-xl transition">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Travel Orders Overview</h2>
                        <div id="supervisorAreaChart" class="w-full h-64 md:h-80"></div>
                    </div>
                </div>

                <!-- ⚡ Quick Actions + 🥧 Pie (Right) -->
                <div class="card bg-base-100 shadow-lg hover:shadow-xl transition">
                    <div class="card-body space-y-4">

                        <!-- Mini Pie -->
                        <div class="bg-base-200 rounded-lg p-3 shadow-inner">
                            <h2 class="font-semibold text-lg mb-2">Travel Summary</h2>
                            <div id="supervisorPie" class="w-full h-48"></div>
                        </div>

                        <div class="divider my-1"></div>

                        <h2 class="card-title mb-2">Quick Actions</h2>
                        <div class="grid gap-2">
                            <a href="{{ route('travellist.index') }}" class="btn btn-primary w-full shadow-md hover:shadow-lg transition">
                                View Travel Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🧭 Scripts -->
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
            <script>
            document.addEventListener('DOMContentLoaded', () => {

                // 📈 Area Chart (Modern & Interactive)
                const areaOptions = {
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        fontFamily: 'inherit'
                    },
                    series: [{
                        name: 'Travel Orders',
                        data: [
                            {{ $janOrders ?? 0 }}, {{ $febOrders ?? 0 }}, {{ $marOrders ?? 0 }},
                            {{ $aprOrders ?? 0 }}, {{ $mayOrders ?? 0 }}, {{ $junOrders ?? 0 }},
                            {{ $julOrders ?? 0 }}, {{ $augOrders ?? 0 }}, {{ $sepOrders ?? 0 }},
                            {{ $octOrders ?? 0 }}, {{ $novOrders ?? 0 }}, {{ $decOrders ?? 0 }}
                        ]
                    }],
                    xaxis: {
                        categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                        labels: {
                            style: { colors: '#6b7280' }
                        },
                        axisBorder: { color: '#e5e7eb' },
                        axisTicks: { color: '#e5e7eb' }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#6b7280' }
                        },
                        min: 0
                    },
                    stroke: { curve: 'smooth', width: 3 },
                    colors: ['#3b82f6'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0,
                            stops: [0, 90, 100]
                        }
                    },
                    dataLabels: { enabled: false },
                    grid: {
                        borderColor: '#e5e7eb',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        theme: 'light',
                        x: { show: true },
                        y: { formatter: (val) => `${val} orders` }
                    }
                };
                const areaChart = new ApexCharts(document.querySelector("#supervisorAreaChart"), areaOptions);
                areaChart.render();

                // 🥧 Mini Pie Chart
                const pieOptions = {
                    chart: {
                        type: 'donut',
                        height: 180,
                    },
                    series: [
                        {{ $pendingTravelOrders }},
                        {{ $approvedTravelOrders }},
                        {{ $cancelledTravelOrders ?? 0 }}
                    ],
                    labels: ['Pending', 'Approved', 'Cancelled'],
                    colors: ['#facc15', '#10b981', '#ef4444'],
                    legend: {
                        position: 'bottom',
                        fontSize: '12px',
                        labels: { colors: '#6b7280' }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
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
                const pieChart = new ApexCharts(document.querySelector("#supervisorPie"), pieOptions);
                pieChart.render();

            });
            </script>

</x-app-layout>
