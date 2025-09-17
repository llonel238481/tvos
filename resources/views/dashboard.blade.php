@php($role = auth()->user()->role ?? null)

        @switch($role)
            @case('Admin')
            @include('dashboards/dashboard-admin')
        @break
        @case('Employee')
            @include('dashboards/dashboard-employee')
            @break
        @case('CEO')
            @include('dashboards/dashboard-ceo')
            @break
        @case('Supervisor')
            @include('dashboards/dashboard-supervisor')
            @break
        @default
        <p>Role not recognized. Please contact support.</p>
    @endswitch