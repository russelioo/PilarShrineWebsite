@php
$title = 'Confirmation Records';
$singular = 'Confirmation Record';
$description = 'View confirmation entries from the sacramental records registry.';
$columns = ['Name','Date Confirmed','Officiant','Certificate No.','Issued'];
$rows = [['Miguel Reyes','Jun 18, 2024','Bp. Luis Antonio','CON-2024-0088','No'],['Rosa Lim','May 20, 2023','Bp. Luis Antonio','CON-2023-0064','Yes']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))