@php
$title = 'Events Calendar';
$singular = 'Calendar Event';
$description = 'View scheduled liturgical and parish community events.';
$columns = ['Event','Date','Time','Location','Category'];
$rows = [['Youth Formation','Sep 5, 2026','2:00 PM','Parish Hall','Formation'],['Parish Fiesta','Oct 12, 2026','8:00 AM','Shrine Grounds','Community']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))