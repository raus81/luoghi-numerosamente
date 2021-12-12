<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @stack('head')
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3475702324698098" crossorigin="anonymous"></script>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

</head>
<body class="h-100  d-flex flex-column">
<nav class="navbar navbar-expand navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Italia.numerosamente.it</a>

{{--        <ul class="navbar-nav">--}}
{{--            <li class="nav-item dropdown">--}}
{{--                <a class="nav-link dropdown-toggle" href="#" id="navbarDarkDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">--}}
{{--                    Link utili--}}
{{--                </a>--}}
{{--                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="navbarDarkDropdownMenuLink">--}}
{{--                    <li><a class="dropdown-item" href="#">Action</a></li>--}}
{{--                    <li><a class="dropdown-item" href="#">Another action</a></li>--}}
{{--                    <li><a class="dropdown-item" href="#">Something else here</a></li>--}}
{{--                </ul>--}}
{{--            </li>--}}
{{--        </ul>--}}
    </div>
</nav>
<div class="container   comune flex-grow-1">
@yield('content')
</div>
<footer class="text-white flex-shrink-0 text-center p-2">
    Copyright &copy; {{date('Y')}}
</footer>
@yield('script')
</body>
</html>
