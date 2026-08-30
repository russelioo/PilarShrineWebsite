@php
$title = 'Profile / Settings';
$description = 'View your parishioner account details and preferences.';
$columns = ['Setting','Current Value','Status'];
$rows = [['Account Name','Juan Dela Cruz','Active'],['Email','juan@email.com','Verified'],['Notifications','Email and SMS','Enabled']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))