@php
$title = 'Forms';
$singular = 'Form';
$description = 'Manage configurable forms available to parishioners.';
$columns = ['Title','Description','Created By','Active','Created'];
$rows = [['Baptism Request','Request preparation for baptism','Maria Santos','Yes','Aug 20, 2026'],['General Inquiry','Contact the parish office','Pedro Cruz','Yes','Aug 21, 2026']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))