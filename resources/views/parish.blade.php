<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Our Lady of the Pillar Shrine parish website">
    <link rel="icon" type="image/png" href="/images/pilar-shrine-logo.png?v=2">
    <title>Our Lady of the Pillar Shrine</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @php
        $authUser = auth()->user();
        $isParishAdmin = false;
        if ($authUser) {
            $pos = strtolower($authUser->position ?? '');
            $name = strtolower($authUser->name ?? '');
            $email = strtolower($authUser->email ?? '');
            $isParishAdmin = str_contains($pos, 'parish administrator')
                || str_contains($name, 'parish administrator')
                || $email === 'admin@pilarshrine.test';
        }

        $avatar = $authUser?->avatar;
        if (empty($avatar) && $isParishAdmin) {
            $avatar = '/images/pilar-shrine-crest.jpg';
        }

        $authPayload = $authUser ? [
            'id' => $authUser->id,
            'name' => $authUser->name,
            'first_name' => $authUser->first_name,
            'last_name' => $authUser->last_name,
            'email' => $authUser->email,
            'avatar' => $avatar,
            'role' => $authUser->role,
            'is_parish_administrator' => $isParishAdmin,
            'is_complete' => $authUser->isProfileComplete(),
        ] : null;
    @endphp
    <script>
        window.__AUTH_USER__ = {{ Illuminate\Support\Js::from($authPayload) }};
        window.__SITE_SETTINGS__ = {{ Illuminate\Support\Js::from($siteSettings) }};
    </script>
    @vite('resources/js/parish.js')
</head>
<body>
    <div id="app"></div>
</body>
</html>
