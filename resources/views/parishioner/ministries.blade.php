@php
$title = 'Ministries';
$description = 'Explore parish ministries and participation opportunities.';
$columns = ['Ministry','Meeting','Coordinator','Status'];
$rows = [['Music Ministry','Saturday, 3:00 PM','Maria Santos','Open'],['Youth Ministry','Second Sunday','Pedro Cruz','Open'],['Social Action Ministry','Monthly','Ana Flores','Open']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))