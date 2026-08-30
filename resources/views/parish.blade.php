<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Our Lady of the Pillar Shrine parish website">
    <link rel="icon" type="image/png" href="/images/pilar-shrine-logo.png?v=2">
    <title>Our Lady of the Pillar Shrine</title>
    @vite('resources/js/parish.js')
</head>
<body>
    <div id="app"></div>
</body>
</html>
