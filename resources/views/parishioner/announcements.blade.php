@php
$title = 'Announcements';
$description = 'Read the latest notices from Pilar Shrine.';
$columns = ['Announcement','Category','Published','Priority'];
$rows = [['Sunday Mass Advisory','Schedule','Aug 29, 2026','High'],['Choir Auditions','Ministry','Aug 26, 2026','Medium']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))