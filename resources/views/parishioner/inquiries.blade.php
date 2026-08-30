@php
$title = 'My Inquiries';
$description = 'Review questions and messages sent to the parish office.';
$columns = ['Subject','Sent','Last Reply','Status'];
$rows = [['Baptism seminar schedule','Aug 27, 2026','Aug 28, 2026','Answered'],['Certificate availability','Aug 29, 2026','—','Open']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))