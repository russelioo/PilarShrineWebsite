@php
$title = 'Form Submissions';
$singular = 'Submission';
$description = 'Review submitted form data and processing status.';
$columns = ['Form','Submitted By','Summary','Status','Submitted At'];
$rows = [['Baptism Request','Juan Dela Cruz','Preferred date: Sep 12','Pending','Aug 29, 2026 9:20 AM'],['General Inquiry','Guest','Choir membership inquiry','Approved','Aug 28, 2026 4:05 PM']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))