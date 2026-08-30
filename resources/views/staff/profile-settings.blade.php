@php
$title = 'Profile / Settings';
$singular = 'Setting';
$description = 'View your staff profile and portal preferences.';
$columns = ['Setting','Current Value','Status'];
$rows = [['Account Name','Maria Santos','Active'],['Role','Staff','Verified'],['Email Notifications','Enabled','Active']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))