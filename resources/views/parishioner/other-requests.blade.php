@extends('layouts.parishioner')
@section('title', 'Other Requests')
@section('content')
<div class="page-header">
    <div>
        <h2>Other Requests</h2>
        <p class="page-description">Submit other parish service requests and document concerns.</p>
    </div>
    <span class="static-label">Available Soon</span>
</div>

<div class="coming-soon-banner" role="alert">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
    <div>
        <strong>Notice: Online Service &amp; Document Requests will be available soon</strong>
        <p>Online requests for certificates, house blessings, and pastoral appointments are currently being integrated. Please visit or contact the parish office for immediate concerns.</p>
    </div>
</div>

<div class="module-summary">
    <article><strong>3</strong><span>Upcoming Services</span></article>
    <article><strong>Office</strong><span>Direct Assistance</span></article>
    <article><strong>Portal</strong><span>Parishioner service</span></article>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Request Type</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Certificate Request</td>
                <td>Request a baptismal, confirmation, or marriage certificate</td>
                <td><span class="badge-status-soon">Available Soon</span></td>
            </tr>
            <tr>
                <td>House Blessing</td>
                <td>Schedule a priest for home or business blessing</td>
                <td><span class="badge-status-soon">Available Soon</span></td>
            </tr>
            <tr>
                <td>Special Pastoral Request</td>
                <td>Send a pastoral or administrative request</td>
                <td><span class="badge-status-soon">Available Soon</span></td>
            </tr>
        </tbody>
    </table>
</div>
<p class="static-note">Online submission for these requests will be activated soon. For urgent requests, please contact the parish office.</p>
@endsection

@push('styles')
<style>
.coming-soon-banner{display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-radius:10px;background:#fff8e6;border:1px solid #fde68a;color:#b45309;margin-bottom:20px}.coming-soon-banner svg{flex-shrink:0;margin-top:2px;color:#d97706}.coming-soon-banner strong{display:block;font-size:13px;font-family:Georgia,serif;margin-bottom:3px;color:#92400e}.coming-soon-banner p{margin:0;font-size:11px;line-height:1.5;color:#b45309}
.page-header{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:20px}.page-header>div{min-width:0}.page-header h2{margin:0;color:var(--navy);font:700 26px Georgia}.page-description{margin:6px 0 0;color:var(--muted);font-size:11px}.static-label{padding:7px 10px;border-radius:20px;background:#fff8e6;border:1px solid #fde68a;color:#b45309;font-size:9px;font-weight:700;text-transform:uppercase}.module-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px}.module-summary article{padding:16px 18px;border:1px solid var(--line);border-radius:9px;background:#fff}.module-summary strong,.module-summary span{display:block}.module-summary strong{color:var(--navy);font:700 20px Georgia,serif}.module-summary span{margin-top:5px;color:var(--muted);font-size:9px;text-transform:uppercase}.table-wrap{overflow-x:auto;border:1px solid var(--line);border-radius:11px;background:#fff}table{width:100%;border-collapse:collapse;font-size:11px}th,td{padding:13px 15px;text-align:left;border-bottom:1px solid #edf2f7;white-space:nowrap}th{background:#f7fafc;color:var(--muted);font-size:9px;text-transform:uppercase}tbody tr:last-child td{border-bottom:0}.badge-status-soon{padding:4px 8px;border-radius:12px;background:#fff8e6;color:#b45309;font-size:9px;font-weight:700}.static-note{margin-top:12px;color:var(--muted);font-size:10px}@media(max-width:620px){.module-summary{grid-template-columns:1fr}.page-header{display:block}.page-header .static-label{display:inline-block;margin-top:10px}}
</style>
@endpush
