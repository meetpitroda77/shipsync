@extends('layouts.customer')

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
            <div class="container-fluid ">
                <!--begin::Row-->
            <div class="row ">

                <div class="col-lg-3 col-12">
                    <a href="{{ route("customer.shipments") }}" class="text-decoration-none">

                        <div class="info-box bg-primary">`

                            <span class="info-box-icon"><i class="bi bi-box"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Shipments</span>
                                <span class="info-box-number">{{ $totalShipments }}</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3 col-12">
                    <a href="{{ route('customer.shipments', ['status' => 'delivered']) }}" class="text-decoration-none">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="bi bi-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text ">Delivered Shipments</span>
                                <span class="info-box-number">{{ $DeliveredShipments }}</span>
                            </div>
                        </div>
                    </a>

                </div>





                <div class="col-lg-3 col-12">
                    <a href="{{ route('customer.shipments', ['status' => 'ongoingShipments']) }}" class="text-decoration-none">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="bi bi-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Ongoing Shipments</span>
                                <span class="info-box-number">{{ $ongoingShipments }}</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3 col-12">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="bi bi-currency-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">total Spent</span>
                                <span class="info-box-number">{{ $totalSpent }}</span>
                            </div>
                        </div>
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