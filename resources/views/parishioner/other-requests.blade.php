@php
$title = 'Other Requests';
$description = 'Submit other parish service requests and document concerns.';
$columns = ['Request Type','Description','Availability'];
$rows = [['Certificate Request','Request a sacramental certificate','Available'],['House Blessing','Ask for a pastoral schedule','Available'],['Prayer Request','Send a prayer intention','Available']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))