@php
$title = 'Messages / Inquiries';
$description = 'View conversations with the parish office.';
$columns = ['From','Subject','Received','Status'];
$rows = [['Parish Office','Baptism request update','Aug 29, 2026','Unread'],['Events Team','Formation reminder','Aug 27, 2026','Read']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))