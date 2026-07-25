@if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance', 'CS_Marketing']))
    @include('dashboard.owner')
@elseif(auth()->user()->hasRole('Branch_Admin'))
    @include('dashboard.branch_admin')
@elseif(auth()->user()->hasRole('Cashier'))
    @include('dashboard.cashier')
@elseif(auth()->user()->hasAnyRole(['Workshop_Admin', 'Workshop_Staff']))
    @include('dashboard.workshop')
@endif
