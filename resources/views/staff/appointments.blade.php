@php
$title = 'Appointments';
$singular = 'Appointment';
$description = 'Review parish service appointments and approval status.';
$columns = ['Parishioner','Service','Preferred Date','Preferred Time','Status','Notes'];
$rows = [['Juan Dela Cruz','Baptism Interview','Sep 2, 2026','9:00 AM','Pending','Bring birth certificate'],['Ana Flores','Wedding Consultation','Sep 4, 2026','2:00 PM','Approved','Initial meeting']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))