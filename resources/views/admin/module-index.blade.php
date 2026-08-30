@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="page-header">
  <div><h2>{{ $title }}</h2><p class="page-description">{{ $description }}</p></div>
  <button class="btn btn-primary" type="button">+ Add {{ $singular }}</button>
</div>
<div class="module-summary">
  <article><strong>{{ count($rows) }}</strong><span>Sample records</span></article>
  <article><strong>{{ count($columns) }}</strong><span>Visible fields</span></article>
  <article><strong>Static</strong><span>Current data source</span></article>
</div>
<div class="toolbar">
  <input type="search" placeholder="Search {{ strtolower($title) }}..." aria-label="Search {{ strtolower($title) }}">
  <select aria-label="Filter records"><option>All records</option><option>Active</option><option>Pending</option></select>
  <button class="btn btn-outline" type="button">Export</button>
</div>
<div class="table-wrap">
  <table>
    <thead><tr>@foreach($columns as $column)<th>{{ $column }}</th>@endforeach<th>Actions</th></tr></thead>
    <tbody>@foreach($rows as $row)<tr>@foreach($row as $value)<td>{{ $value }}</td>@endforeach<td class="row-actions"><a href="#">View</a><a href="#">Edit</a></td></tr>@endforeach</tbody>
  </table>
</div>
<p class="static-note">Demo content based on the current database migration. CRUD actions will be connected later.</p>
@endsection
@push('styles')
<style>
.page-header>div{min-width:0}.page-description{margin:6px 0 0;color:var(--muted);font-size:11px}.module-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px}.module-summary article{padding:16px 18px;border:1px solid var(--line);border-radius:9px;background:#fff}.module-summary strong,.module-summary span{display:block}.module-summary strong{color:var(--navy);font:700 20px Georgia,serif}.module-summary span{margin-top:5px;color:var(--muted);font-size:9px;text-transform:uppercase}.toolbar{display:flex;gap:10px;margin-bottom:18px}.toolbar input,.toolbar select{padding:10px 12px;border:1px solid var(--line);border-radius:7px;background:#fff;font-size:11px}.toolbar input{flex:1;min-width:180px}.table-wrap{overflow-x:auto;border:1px solid var(--line);border-radius:11px;background:#fff}table{width:100%;border-collapse:collapse;font-size:11px}th,td{padding:13px 15px;text-align:left;border-bottom:1px solid #edf2f7;white-space:nowrap}th{background:#f7fafc;color:var(--muted);font-size:9px;text-transform:uppercase}tbody tr:last-child td{border-bottom:0}.row-actions{display:flex;gap:10px}.row-actions a{color:var(--blue);font-weight:700;text-decoration:none}.static-note{color:var(--muted);font-size:10px}@media(max-width:620px){.page-header{align-items:flex-start;gap:12px}.module-summary{grid-template-columns:1fr}.toolbar{flex-wrap:wrap}.toolbar input{min-width:100%}}
</style>
@endpush