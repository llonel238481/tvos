<x-app-layout>
    <div class="py-10 bg-base-200 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-emerald-600">Admin Dashboard</h1>
                <p class="text-sm text-gray-500">Overview of travel order activity</p>
            </div>

                <!-- Combined Dashboard Card (Stats Top + Recent Bottom) -->
                <div class="card bg-base-100 shadow-sm border border-base-300">
                    <div class="card-body space-y-8">
                        <!-- 📊 Stats Section -->
                        <div>
                            <h2 class="font-semibold text-lg mb-4">Travel Statistics</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <!-- Total Orders -->
                                <div class="stats shadow-lg bg-base-100">
                                    <div class="stat">
                                        <div class="stat-title">Total Orders</div>
                                        <div class="stat-value text-primary">{{ $totalTravelOrders }}</div>
                                        <div class="stat-desc">As of {{ now()->format('F d, Y') }}</div>
                                    </div>
                                </div>

                                <div class="stats shadow-lg bg-base-100">
                                    <div class="stat">
                                        <div class="stat-title">Cancelled</div>
                                        <div class="stat-value text-error">{{ $cancelledTravelOrders  }}</div>
                                        <div class="stat-desc">As of {{ now()->format('F d, Y') }}</div>
                                    </div>
                                </div>

                                <div class="stats shadow-lg bg-base-100">
                                    <div class="stat">
                                        <div class="stat-title">Approved</div>
                                        <div class="stat-value text-success">{{ $approvedTravelOrders  }}</div>
                                        <div class="stat-desc">Approved orders</div>
                                    </div>
                                </div>

                            </div>
                        </div>

                       <!-- 📝 Recent Activity Section -->
                        <div>
                            <h2 class="font-semibold text-lg mb-4">Recent Activity</h2>
                            <div class="space-y-4 max-h-64 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300">
                                @php
                                    $recentTravels = \App\Models\Travel_Lists::latest()->take(3)->get();
                                @endphp

                                @forelse ($recentTravels as $travel)
                                    <div class="flex items-start space-x-3">
                                        <!-- Timeline Dot -->
                                        <div class="mt-2 w-3 h-3 rounded-full 
                                            @if($travel->status == 'Pending') bg-yellow-500
                                            @elseif($travel->status == 'Approved') bg-emerald-600
                                            @elseif($travel->status == 'Cancelled') bg-red-500
                                            @else bg-gray-400
                                            @endif">
                                        </div>

                                        <!-- Activity Content -->
                                        <div class="flex-1">
                                            <div class="flex justify-between items-start">
                                                <p class="font-medium text-gray-800 leading-tight">{{ $travel->purpose }}</p>
                                                <span class="text-xs text-gray-500">
                                                    {{ \Carbon\Carbon::parse($travel->travel_date)->format('M d, Y') }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                Destination: <span class="font-semibold">{{ $travel->destination }}</span>
                                            </p>
                                            <span class="inline-block text-xs mt-1 px-2 py-1 rounded-full 
                                                @if($travel->status == 'Pending') bg-yellow-100 text-yellow-800
                                                @elseif($travel->status == 'Approved') bg-emerald-100 text-emerald-800
                                                @elseif($travel->status == 'Cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ $travel->status }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-gray-500 py-4">
                                        No recent travel activity found
                                    </div>
                                @endforelse
                            </div>

                            <!-- View All Button -->
                           <!-- View All Link -->
                            @if(\App\Models\Travel_Lists::count() > 3)
                                <div class="text-center mt-2">
                                    <a href="{{ route('travellist.index') }}" class="text-blue-600 hover:underline text-sm">
                                        View All
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Chart (Left) + Quick Actions (Right, shorter) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- 📊 Chart Card (Left, 2 columns wide) -->
                    <div class="card bg-base-100 shadow-sm border border-base-300 lg:col-span-2">
                        <div class="card-body">
                            <h2 class="font-semibold text-lg mb-4">Monthly Travel Orders</h2>
                            <div id="apexChart" class="w-full h-64 md:h-80"></div>
                        </div>
                    </div>

                    <!-- ⚡ Quick Actions Card (Right, auto height) -->
                    <div class="flex lg:items-start">
                        <div class="card bg-base-100 shadow-sm border border-base-300 w-full lg:w-80">
                            <div class="card-body">
                                <h2 class="font-semibold text-lg mb-4">Quick Actions</h2>
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
                </div>


                <!-- ApexCharts -->
                <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
                <script>
                document.addEventListener('DOMContentLoaded', () => {
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
                                {{ $janOrders }},
                                {{ $febOrders }},
                                {{ $marOrders }},
                                {{ $aprOrders }},
                                {{ $mayOrders }},
                                {{ $junOrders }},
                                {{ $julOrders }},
                                {{ $augOrders }},
                                {{ $sepOrders }},
                                {{ $octOrders }},
                                {{ $novOrders }},
                                {{ $decOrders }}
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
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
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
                        tooltip: {
                            theme: 'light'
                        }
                    };

                    const chart = new ApexCharts(document.querySelector("#apexChart"), options);
                    chart.render();
                });
                </script>




                

        </div>
    </div>
</x-app-layout>
