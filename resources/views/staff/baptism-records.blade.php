@php
$title = 'Baptism Records';
$singular = 'Baptism Record';
$description = 'View baptism entries from the sacramental records registry.';
$columns = ['Name','Date Baptized','Officiating Priest','Certificate No.','Issued'];
$rows = [['Maria Santos','May 12, 2002','Fr. Pedro Lim','BAP-2002-0142','Yes'],['Jose Rivera','Jun 8, 2010','Fr. Ramon Cruz','BAP-2010-0081','Yes']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))