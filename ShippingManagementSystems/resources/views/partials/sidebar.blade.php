@php
  $role = Auth::user()->role;
@endphp

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">

  <div class="sidebar-brand">
    <a href="{{ route($role) }}" class="brand-link">
      <img src="{{ Vite::asset('resources/img/shipcync.png') }}" class="brand-image opacity-75 shadow" />
    </a>
  </div>

  <div class="sidebar-wrapper">
    <nav class="mt-2">

      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation"
        data-accordion="false" id="navigation">

        <li class="nav-item">
          <a href="{{ route($role) }}" class="nav-link active">
            <i class="nav-icon bi bi-house-door"></i>
            <p>Dashboard</p>
          </a>
        </li>

        @if(in_array($role, ['customer', 'admin', 'staff', 'agent']))
          <li class="nav-item">



            <a href="#" class="nav-link">
              <p>
                Shipment management
                <i class="nav-arrow bi bi-chevron-right"></i>
              </p>
            </a>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route("{$role}.shipments") }}" class="nav-link">
                  <i class="nav-icon bi bi-table"></i>
                  <p> Shipments</p>
                </a>
              </li>
              @if(in_array($role, ['customer', 'admin']))

                <li class="nav-item">
                  <a href="{{ route("{$role}.createShipment") }}" class="nav-link">
                    <i class="nav-icon bi bi-plus"></i>
                    <p>Create Shipment</p>
                  </a>
                </li>
              @endif

            </ul>
          </li>
        @endif


        {{-- @if(in_array($role, ['staff', 'agent', 'admin']))

        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-table"></i>
            <p>
              Tables
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>

          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="#" class="nav-link">
                <p>Simple Tables</p>
              </a>
            </li>
          </ul>
        </li>

        @endif --}}





        @if(in_array($role, ['customer', 'admin']))

          <li class="nav-item">
            <a href="{{ route("{$role}.shipment.track.form") }}" class="nav-link">

              <i class="nav-icon bi bi-search"></i>
              <p>Track Shipment</p>
            </a>
          </li>



        @endif

        @if(in_array($role, ['customer', 'admin', 'staff', 'agent']))

          <li class="nav-item">
            <a href="{{ route("{$role}.shipment.paymentShipments") }}" class="nav-link">

              <i class="nav-icon bi bi-currency-dollar"></i>
              <p>Payment Shipments</p>
            </a>
          </li>

        @endif
        @if(in_array($role, ['admin']))

          <li class="nav-item">
            <a href="{{ route("{$role}.users.index") }}" class="nav-link">

              <i class="nav-icon bi bi-people"></i>
              <p>Users</p>
            </a>
          </li>

          <li class="nav-item">

            <a href="{{ route("getsetting") }}" class="nav-link">

              <i class="nav-icon bi bi-gear"></i>
              <p>Settings</p>
            </a>

          </li>

          <li class="nav-item">


            <a href="#" class="nav-link">
              <p>
                Reports
                <i class="nav-arrow bi bi-chevron-right"></i>
              </p>
            </a>



            <ul class="nav nav-treeview">

              <li class="nav-item">
                <a href="{{ route("admin.reports") }}" class="nav-link">

                  <i class="nav-icon bi bi-bar-chart"></i>
                  <p>Daily Reports</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route("reportgeneral") }}" class="nav-link">

                  <i class="nav-icon bi bi-bar-chart"></i>
                  <p>General Reports</p>
                </a>
              </li>


            </ul>
          <li>


        @endif


          @if(in_array($role, ['customer', 'admin']))

            <li class="nav-item">
              <a href="{{ route("{$role}.recipient.index") }}" class="nav-link">

                <i class="nav-icon bi bi-person-lines-fill"></i>
                <p>Recipients</p>
              </a>
            </li>
          @endif


      </ul>

    </nav>
  </div>
</aside>