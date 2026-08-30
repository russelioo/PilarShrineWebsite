@php
$title = 'Mass Intentions';
$description = 'Track the Mass intentions you have submitted.';
$columns = ['Intention','Preferred Mass','Submitted','Status'];
$rows = [['Thanksgiving for the Santos Family','Sep 6, 2026 - 7:30 AM','Aug 28, 2026','Pending'],['For Elena Cruz','Aug 30, 2026 - 5:00 PM','Aug 24, 2026','Offered']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))