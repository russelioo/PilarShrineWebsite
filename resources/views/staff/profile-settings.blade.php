@php
$user = auth()->user() ?? \App\Models\User::where('email', 'soredajohnrussel15@gmail.com')->first() ?? \App\Models\User::where('role', 'staff')->latest()->first();

$title = 'Profile / Settings';
$singular = 'Setting';
$description = 'View your staff profile and portal preferences.';
$columns = ['Setting','Current Value','Status'];
$rows = [
    ['Account Name', $user?->name ?: 'John Russel Soreda', $user?->is_verified ? 'Active' : 'Pending'],
    ['Email Address', $user?->email ?: 'soredajohnrussel15@gmail.com', 'Verified'],
    ['Role', ucfirst($user?->role ?: 'staff'), 'Verified'],
    ['Phone Number', $user?->phone ?: '—', 'Active'],
    ['Email Notifications', 'Enabled', 'Active'],
];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))