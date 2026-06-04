@extends('layouts.agent')

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
                <!--begin::Row-->

                <div class="row">

                    <div class="col-lg-4 col-12">
                        <a href="{{ route("agent.shipments", ['status' => 'delivered_today']) }}"
                            class="text-decoration-none">

                            <div class="info-box bg-primary">

                                <span class="info-box-icon"><i class="bi bi-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Delivered Shipments Today</span>
                                    <span class="info-box-number">{{ $deliveredShipmentsToday }}</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-lg-4 col-12">
                        <a href="{{ route('agent.shipments') }}" class="text-decoration-none">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="bi bi-person-lines-fill"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text ">assigned Shipments</span>
                                    <span class="info-box-number">{{ $assignedShipments }}</span>
                                </div>
                            </div>
                        </a>

                    </div>





                    <div class="col-lg-4 col-12">
                        <a href="{{ route('agent.shipments', ['status' => 'pending_delivery']) }}"
                            class="text-decoration-none">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="bi bi-truck"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">pending Shipments for Delivery</span>
                                    <span class="info-box-number">{{ $pendingShipmentsDelivery }}</span>
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