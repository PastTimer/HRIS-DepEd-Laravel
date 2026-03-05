<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>HRIS System - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/argon.css') }}" type="text/css">
</head>
<body>
    @include('layouts.sidebar')
    
    <main class="main-content">
        @yield('content')
    </main>

    </body>
</html>