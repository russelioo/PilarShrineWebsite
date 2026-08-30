@php
$title = 'Donations';
$description = 'View static donation options and your recent donation history.';
$columns = ['Purpose','Amount','Method','Status'];
$rows = [['Parish Operations','PHP 500.00','GCash','Received'],['Church Maintenance','PHP 1,000.00','Cash','Receipted']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))