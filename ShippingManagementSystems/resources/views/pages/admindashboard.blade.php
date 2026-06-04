@extends('layouts.admin')

@section('content')
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Dashboard</h3>
                    </div>

                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">

                <div class="row">

                    <div class="col-lg-4 col-12">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="bi bi-box-seam"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Shipments</span>
                                <span class="info-box-number">{{ $totalShipments }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-12">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="bi bi-currency-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Revenue</span>
                                <span class="info-box-number">${{ number_format($totalRevenue, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-12">
                        <a href="{{ route('admin.shipments', ['status' => 'ongoingShipments']) }}"
                            class="text-decoration-none">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="bi bi-truck"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Ongoing Shipments</span>
                                    <span class="info-box-number">{{ $ongoingShipments }}</span>
                                </div>
                            </div>
                        </a>
                    </div>



                    <div class="col-lg-4 col-12">
                        <a href="{{ route('admin.shipments', ['status' => 'delivered']) }}" class="text-decoration-none">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="bi bi-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text ">Delivered Shipments</span>
                                    <span class="info-box-number">{{ $completedShipments }}</span>
                                </div>
                            </div>
                        </a>
                    </div>


                    <div class="col-lg-4 col-12">
                        <div class="info-box bg-secondary">
                            <span class="info-box-icon"><i class="bi bi-graph-up"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Delivery Success Rate</span>
                                <span class="info-box-number">{{ number_format($deliverySuccessRate, 2) }}%</span>
                            </div>
                        </div>
                    </div>


                    <div class="col-lg-4 col-12">
                        <div class="info-box bg-info-subtle">
                            <span class="info-box-icon"><i class="bi bi-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Average Delivery Time</span>
                                <span class="info-box-number">{{ number_format($avgDeliveryTime, 2) }} days</span>
                            </div>
                        </div>
                    </div>



                </div>

                <div class="row">
                    <div id="shipments-revenue-chart" data-shipments='@json($shipments)' data-revenue='@json($revenue)'
                        data-months='@json($months)'>
                    </div>



                </div>


            </div>
        </div>
    </main>




    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const shipment_revenue_chart_configure = document.querySelector('#shipments-revenue-chart');

            if (!shipment_revenue_chart_configure) return;

            const shipments = JSON.parse(shipment_revenue_chart_configure.dataset.shipments);
            const revenue = JSON.parse(shipment_revenue_chart_configure.dataset.revenue);
            const months = JSON.parse(shipment_revenue_chart_configure.dataset.months);

            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts not loaded');
                return;
            }

            const maxShipment = Math.max(...shipments);
            const maxRevenue = Math.max(...revenue);

            const options = {
                series: [
                    {
                        name: 'Total Shipments',
                        data: shipments
                    },
                    {
                        name: 'Total Revenue',
                        data: revenue
                    },
                ],
                chart: {
                    type: 'bar',
                    height: 450,
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '10%',
                        endingShape: 'rounded',
                    },
                },
                colors: ['#0d6efd', '#20c997'],
                dataLabels: { enabled: false },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent'],
                },
                xaxis: {
                    categories: months,
                },

                yaxis: [
                    {
                        seriesName: 'Total Shipments',
                        title: { text: 'Shipments' },
                        min: 0,
                        max: maxShipment + 10,
                    },
                    {
                        seriesName: 'Total Revenue',
                        opposite: true,
                        title: { text: 'Revenue' },
                        min: 0,
                        max: maxRevenue + (maxRevenue * 0.1), 
                        labels: {
                            formatter: function (val) {
                                return val.toLocaleString();
                            },
                        },
                    }
                ],

                fill: { opacity: 1 },

                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val.toLocaleString();
                        },
                    },
                },

                legend: { position: 'top' },
            };

            new ApexCharts(shipment_revenue_chart_configure, options).render();

        });
    </script>
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