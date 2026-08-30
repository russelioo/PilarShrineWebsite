@php
$title = 'Events & Schedule';
$description = 'View upcoming Masses, formations, and community events.';
$columns = ['Event','Date','Time','Location'];
$rows = [['Sunday Mass','Sep 6, 2026','7:30 AM','Main Church'],['Youth Formation','Sep 12, 2026','2:00 PM','Parish Hall'],['Parish Fiesta','Oct 12, 2026','8:00 AM','Shrine Grounds']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))