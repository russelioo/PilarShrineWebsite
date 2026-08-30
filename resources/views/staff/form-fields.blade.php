@php
$title = 'Form Fields';
$singular = 'Form Field';
$description = 'Configure fields and requirements for each parish form.';
$columns = ['Form','Field Name','Field Type','Required','Options'];
$rows = [['Baptism Request','Child Full Name','Text','Yes','â€”'],['Baptism Request','Preferred Date','Date','Yes','â€”'],['General Inquiry','Message','Textarea','Yes','â€”']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))