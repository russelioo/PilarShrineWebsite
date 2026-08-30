@php
$title = 'Time Slots';
$singular = 'Time Slot';
$description = 'Review capacity and availability for bookable schedule slots.';
$columns = ['Mass Schedule','Slot Time','Capacity','Bookings','Available'];
$rows = [['Sunday Morning Mass','6:00 AM','50','32','Yes'],['Sunday Morning Mass','7:30 AM','50','50','No']];
@endphp
@include('admin.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))