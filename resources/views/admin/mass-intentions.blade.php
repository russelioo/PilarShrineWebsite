@php
$title = 'Mass Intentions';
$singular = 'Mass Intention';
$description = 'Track requested intentions, offerings, and fulfillment status.';
$columns = ['Requested By','Type','Names','Offering','Requested Date','Status'];
$rows = [['Maria Santos','Thanksgiving','Santos Family','PHP 500.00','Aug 28, 2026','Pending'],['Roberto Cruz','Deceased','Elena Cruz','PHP 300.00','Aug 27, 2026','Offered']];
@endphp
@include('admin.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))