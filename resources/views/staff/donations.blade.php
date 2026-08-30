@php
$title = 'Donations';
$singular = 'Donation';
$description = 'Monitor donations, payment status, and receipt issuance.';
$columns = ['Donor','Amount','Method','Reference','Status','Received By','Receipt'];
$rows = [['Anonymous','PHP 2,500.00','GCash','GC-829104','Paid','Maria Santos','Yes'],['Jose Rivera','PHP 1,000.00','Cash','â€”','Paid','Pedro Cruz','No']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))