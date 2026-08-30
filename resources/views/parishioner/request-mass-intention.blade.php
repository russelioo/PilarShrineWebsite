@php
$title = 'Request Mass Intention';
$description = 'Static request preview for offering a Mass intention.';
$columns = ['Field','Sample Entry','Required'];
$rows = [['Intention Type','Thanksgiving','Yes'],['Name or Intention','Santos Family','Yes'],['Preferred Mass','Sunday - 7:30 AM','Yes']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))