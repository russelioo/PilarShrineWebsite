@php
$title = 'Sacramental Records';
$singular = 'Sacramental Record';
$description = 'Maintain official sacrament and certificate information.';
$columns = ['Parishioner','Record Type','Date Performed','Officiating Priest','Certificate No.','Issued'];
$rows = [['Maria Santos','Baptism','May 12, 2002','Fr. Pedro Lim','BAP-2002-0142','Yes'],['Miguel Reyes','Confirmation','Jun 18, 2024','Bp. Luis Antonio','CON-2024-0088','No']];
@endphp
@include('admin.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))