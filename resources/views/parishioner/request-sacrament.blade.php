@php
$title = 'Baptism / Wedding / Funeral';
$description = 'Static request preview for sacramental and funeral services.';
$columns = ['Service','Typical Requirement','Office Review'];
$rows = [['Baptism','Birth certificate and sponsor details','Required'],['Wedding','Canonical interview documents','Required'],['Funeral','Deceased and schedule information','Required']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))