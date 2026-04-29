@if(Auth::check()) 
    <div id="user-info" 
         data-role="{{ Auth::user()->role }}" 
         data-user-id="{{ Auth::id() }}">
    </div>
@endif
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShipSync</title>
    {{-- <script>
        window.Laravel = {
            userId: {{ auth()->id() }},
        };
    </script> --}}

    @vite([
        'resources/scss/app.scss',
        'resources/js/app.js',
        'resources/js/dashboard.js'
    ])
</head>

<body class="">
    @include('partials.toast')

    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <img src="{{ Vite::asset('resources/img/shipcync.png') }}" alt=" Logo" width="150"
                    class="brand-image " />

            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link active" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('shipment.track.form') }}">Tracking</a></li>
                </ul>

                <div class="d-flex gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-dark rounded-pill px-4">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-dark rounded-pill px-4">Get Started</a>
                </div>
            </div>
        </div>
    </nav>


    @yield('content')



</body>

</html>