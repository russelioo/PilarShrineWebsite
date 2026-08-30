@php
$title = 'Sacraments Schedule';
$singular = 'Sacrament Schedule';
$description = 'View upcoming sacrament schedules and assigned clergy.';
$columns = ['Sacrament','Date','Time','Location','Officiant'];
$rows = [['Baptism','Sep 12, 2026','10:00 AM','Baptistry','Fr. John Reyes'],['Confirmation','Oct 3, 2026','2:00 PM','Main Church','Bp. Luis Antonio']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))