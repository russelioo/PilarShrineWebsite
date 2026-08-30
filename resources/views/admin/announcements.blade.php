@php
$title = 'Announcements';
$singular = 'Announcement';
$description = 'Prepare and schedule parish notices for publication.';
$columns = ['Title','Category','Priority','Published','Expires','Pinned'];
$rows = [['Sunday Mass Advisory','Schedule','High','Aug 29, 2026','Sep 5, 2026','Yes'],['Choir Auditions','Ministry','Medium','Aug 26, 2026','Sep 15, 2026','No']];
@endphp
@include('admin.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))