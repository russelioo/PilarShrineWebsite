@php
$title = 'Notifications';
$singular = 'Notification';
$description = 'Track email, SMS, and push notification delivery.';
$columns = ['Recipient','Type','Subject','Status','Sent At'];
$rows = [['Maria Santos','Email','Appointment approved','Sent','Aug 29, 2026 10:00 AM'],['Juan Dela Cruz','SMS','Mass intention received','Pending','â€”']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))