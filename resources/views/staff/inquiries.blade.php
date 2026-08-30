@php
$title = 'Inquiries';
$singular = 'Inquiry';
$description = 'View questions and messages received by the parish office.';
$columns = ['Name','Subject','Channel','Received','Status'];
$rows = [['Rosa Lim','Choir membership','Email','Aug 29, 2026','New'],['Mark Reyes','Office hours','Website','Aug 28, 2026','Answered']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))