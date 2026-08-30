@php
$title = 'Sacrament Requests';
$singular = 'Sacrament Request';
$description = 'Review submitted requests for parish sacraments.';
$columns = ['Requester','Sacrament','Preferred Date','Contact','Status'];
$rows = [['Juan Dela Cruz','Baptism','Sep 12, 2026','0917 123 4567','Pending'],['Ana Flores','Marriage','Oct 8, 2026','0936 123 4567','Approved']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))