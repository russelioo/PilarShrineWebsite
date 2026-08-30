@php
$title = 'Reports';
$singular = 'Report';
$description = 'View basic operational summaries for parish staff.';
$columns = ['Report','Period','Total','Last Updated'];
$rows = [['Mass Intentions','August 2026','36','Aug 30, 2026'],['Sacrament Requests','August 2026','18','Aug 30, 2026'],['Upcoming Events','Next 30 days','7','Aug 30, 2026']];
@endphp
@include('staff.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))