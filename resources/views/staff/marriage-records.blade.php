@php
$title = 'Marriage Records';
$singular = 'Marriage Record';
$description = 'View marriage entries from the sacramental records registry.';
$columns = ['Couple','Date Married','Officiating Priest','Certificate No.','Issued'];
$rows = [['Ana Flores & Miguel Reyes','Jun 18, 2024','Fr. John Reyes','MAR-2024-0038','Yes'],['Liza Tan & Carlo Cruz','Jul 6, 2025','Fr. Ramon Cruz','MAR-2025-0045','Yes']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))