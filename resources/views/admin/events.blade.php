@php
$title = 'Events';
$singular = 'Event';
$description = 'Plan parish activities, dates, venues, and recurrence.';
$columns = ['Title','Date','Start','End','Location','Type','Recurring'];
$rows = [['Parish Fiesta','Oct 12, 2026','8:00 AM','8:00 PM','Shrine Grounds','Community','No'],['Youth Formation','Sep 5, 2026','2:00 PM','4:00 PM','Parish Hall','Formation','Yes']];
@endphp
@include('admin.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))