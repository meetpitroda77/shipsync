@if(Auth::check()) 
    <div id="user-info" 
         data-role="{{ Auth::user()->role }}" 
         data-user-id="{{ Auth::id() }}">
    </div>
@endif
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.Laravel = {
            userId: {{ auth()->id() }},
        };
    </script>

    <title>Agent Panel</title>
    @vite([
        'resources/scss/app.scss',
        'resources/js/app.js',
        'resources/js/dashboard.js'
    ])




</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    @include('partials.toast')


    <div class="app-wrapper">

        @include('partials.navbar')

        @include('partials.sidebar')

        <main class="app-main">
            <div class="container-fluid pt-3">
                @yield('content')
            </div>
        </main>

        @include('partials.footer')

    </div>

</body>

</html>