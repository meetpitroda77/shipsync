@extends('layouts.staff')

@section('content')
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Dashboard</h3>
                </div>

            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content Header-->
    <!--begin::App Content-->
    <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
            <div class="row">

                <div class="col-lg-3 col-12">
                    <a href="{{ route("staff.shipments", ['status' => 'delivered_today']) }}" class="text-decoration-none">

                        <div class="info-box bg-primary">

                            <span class="info-box-icon"><i class="bi bi-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Delivered Today</span>
                                <span class="info-box-number">{{ $DeliveredToday }}</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3 col-12">
                    <a href="{{ route('staff.shipments', ['status' => 'delayed']) }}" class="text-decoration-none">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="bi bi-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text ">Delayed Shipments</span>
                                <span class="info-box-number">{{ $DelayedShipments }}</span>
                            </div>
                        </div>
                    </a>

                </div>





                <div class="col-lg-3 col-12">
                    <a href="{{ route('staff.shipments', ['status' => 'pending_assigned']) }}" class="text-decoration-none">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="bi bi-people"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pending Assignments</span>
                                <span class="info-box-number">{{ $PendingAssignments }}</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3 col-12">
                    <a href="{{ route('staff.shipments', ['status' => 'failed_delivery']) }}" class="text-decoration-none">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="bi bi-x-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">failed Deliveries</span>
                                <span class="info-box-number">{{ $failedDeliveries }}</span>
                            </div>
                        </div>
                    </a>
                </div>



            </div>

        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
</main>




<script>
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

        // Disable OverlayScrollbars on mobile devices to prevent touch interference
        const isMobile = window.innerWidth <= 992;

        if (
            sidebarWrapper &&
            OverlayScrollbars?.OverlayScrollbars !== undefined &&
            !isMobile
        ) {
            OverlayScrollbars.OverlayScrollbars(sidebarWrapper, {
                scrollbars: {
                    theme: Default.scrollbarTheme,
                    autoHide: Default.scrollbarAutoHide,
                    clickScroll: Default.scrollbarClickScroll,
                },
            });
        }
    });
</script>
@endsection