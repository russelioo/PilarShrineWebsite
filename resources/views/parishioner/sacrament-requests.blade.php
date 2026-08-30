@php
$title = 'Sacrament Requests';
$description = 'View your baptism, wedding, funeral, and other sacrament requests.';
$columns = ['Sacrament','For','Preferred Date','Status'];
$rows = [['Baptism','Sofia Dela Cruz','Sep 12, 2026','Pending'],['Marriage','Ana Flores & Miguel Reyes','Oct 8, 2026','Approved']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))