@extends('layouts.landinglayout')

@section('content')
    <section class="d-flex align-items-center justify-content-center" style="height: 100vh; overflow: hidden;">
        <div class="container py-5">

            <div class="row g-5">
                <div class="col-md-6 order-2 order-md-1 m-auto">

                    <h1 class="fw-bold display-4 responsive-heading">
                        ShipSync Streamline Your <br class="d-none d-md-block">
                        Shipping Operations
                    </h1>

                    <p class="text-muted mt-4 responsive-text">
                        Effortlessly manage your logistics and track shipments in real-time
                        with ShipSync, your ultimate shipping dashboard.
                    </p>

                    <div class="mt-4">
                        <a href="{{ route('register') }}" class="btn btn-dark rounded-pill px-4">Get Started</a>
                    </div>

                </div>
                <div class="col-md-6 m-auto order-1 order-md-2">
                    <img src="{{ Vite::asset('resources/img/logistic_best.webp') }}" class="img-fluid">
                </div>
            </div>

        </div>
    </section>
@endsection

@section('styles')
    <style>
        .responsive-heading {
            font-size: 2.5rem;
        }

        .responsive-text {
            font-size: 1rem;
        }

        @media (min-width: 768px) {
            .responsive-heading {
                font-size: 3rem;
            }

            .responsive-text {
                font-size: 1.1rem;
            }
        }

        @media (min-width: 992px) {
            .responsive-heading {
                font-size: 4rem;
            }

            .responsive-text {
                font-size: 1.2rem;
            }
        }
    </style>
@endsection