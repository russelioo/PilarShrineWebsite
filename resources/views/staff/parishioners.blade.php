@php
$title = 'Parishioners';
$singular = 'Parishioner';
$description = 'View registered parishioner contact and account information.';
$columns = ['Name','Email','Phone','Status','Last Login'];
$rows = [['Juan Dela Cruz','juan@email.com','0917 123 4567','Active','Aug 28, 2026'],['Maria Reyes','maria@email.com','0928 321 4567','Active','Aug 27, 2026']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))