<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Our Lady of the Pillar Shrine parish website">
    <link rel="icon" type="image/png" href="/images/pilar-shrine-logo.png?v=2">
    <title>Our Lady of the Pillar Shrine</title>
    @php
        $authPayload = auth()->check() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'first_name' => auth()->user()->first_name,
            'last_name' => auth()->user()->last_name,
            'email' => auth()->user()->email,
            'avatar' => auth()->user()->avatar,
            'role' => auth()->user()->role,
            'is_complete' => auth()->user()->isProfileComplete(),
        ] : null;
    @endphp
    <script>
        window.__AUTH_USER__ = {!! json_encode($authPayload) !!};
    </script>
    @vite('resources/js/parish.js')
</head>
<body>
    <div id="app"></div>
</body>
</html>
