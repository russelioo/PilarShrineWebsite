@php
$title = 'Mass Schedules';
$singular = 'Mass Schedule';
$description = 'Manage recurring Mass times, locations, and assigned priests.';
$columns = ['Title','Day','Start','End','Location','Priest','Active'];
$rows = [['Sunday Morning Mass','Sunday','6:00 AM','7:00 AM','Main Church','Fr. John Reyes','Yes'],['Weekday Mass','Monday-Friday','6:30 AM','7:15 AM','Adoration Chapel','Fr. Ramon Cruz','Yes']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))