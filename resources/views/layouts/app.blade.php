<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name', 'Kasir Laravel') }}</title>
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}" />
</head>
<body>
    @yield('content')
</body>
</html>
