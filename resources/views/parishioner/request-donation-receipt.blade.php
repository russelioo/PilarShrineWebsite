@extends('layouts.parishioner')
@section('title', 'Request Donation Acknowledgment / Receipt')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Parish Stewardship</p>
        <h2>Request Donation Acknowledgment / Receipt</h2>
        <p>Submit your donation verification details. The parish office will verify your contribution and issue an official acknowledgment.</p>
    </div>
    <a class="btn btn-outline" href="{{ route('parishioner.donations') }}">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span>View My Donations</span>
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Please correct the errors below:</strong>
        <ul style="margin: 6px 0 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-container-card">
    <form class="donation-receipt-form" method="POST" action="{{ route('parishioner.donations.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Section 1: Donor Identification -->
        <div class="form-section-header">
            <span class="step-badge">1</span>
            <div>
                <h3>Donor Information</h3>
                <p>Confirm the details of the donor to appear on the parish acknowledgment or receipt.</p>
            </div>
        </div>

        <div class="fields-grid">
            <label class="form-field">
                <span class="field-label">Donor Name <strong class="req">*</strong></span>
                <input
                    type="text"
                    name="donor_name"
                    value="{{ old('donor_name', $user->name ?? '') }}"
                    placeholder="Full name or organization"
                    required
                />
                <small class="field-hint">Name to be inscribed on the official church receipt.</small>
                @error('donor_name')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="form-field">
                <span class="field-label">Email Address <strong class="req">*</strong></span>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email ?? '') }}"
                    placeholder="your.email@example.com"
                    required
                />
                <small class="field-hint">We will send acknowledgment updates to this email.</small>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="form-field">
                <span class="field-label">Contact Number</span>
                <input
                    type="tel"
                    name="contact_number"
                    value="{{ old('contact_number', $user->phone ?? '') }}"
                    placeholder="0912 345 6789"
                />
                <small class="field-hint">For quick verification by the parish financial office.</small>
                @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="section-divider"></div>

        <!-- Section 2: Transaction Details -->
        <div class="form-section-header">
            <span class="step-badge">2</span>
            <div>
                <h3>Payment &amp; Transaction Details</h3>
                <p>Provide the payment method, amount, and reference code of your transfer.</p>
            </div>
        </div>

        <div class="payment-channels-hint">
            <div class="hint-item">
                <strong>GCash:</strong> <span>09214309753</span> <small>(JOSE BURT SARE)</small>
            </div>
            <div class="hint-item">
                <strong>InstaPay:</strong> <span>Official QR Ph Code</span> <small>(JOSE BURT SARE)</small>
            </div>
        </div>

        <div class="fields-grid">
            <label class="form-field">
                <span class="field-label">Amount Donated (PHP) <strong class="req">*</strong></span>
                <div class="input-with-prefix">
                    <span class="input-prefix">₱</span>
                    <input
                        type="number"
                        name="amount"
                        step="0.01"
                        min="1"
                        value="{{ old('amount') }}"
                        placeholder="0.00"
                        required
                    />
                </div>
                <small class="field-hint">Exact amount sent in Philippine Pesos (PHP).</small>
                @error('amount')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="form-field">
                <span class="field-label">Date of Donation <strong class="req">*</strong></span>
                <input
                    type="date"
                    name="donation_date"
                    max="{{ date('Y-m-d') }}"
                    value="{{ old('donation_date', date('Y-m-d')) }}"
                    required
                />
                <small class="field-hint">Date the transaction was executed.</small>
                @error('donation_date')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="form-field">
                <span class="field-label">Payment Method <strong class="req">*</strong></span>
                <select name="method" required>
                    <option value="">Select payment method</option>
                    <option value="GCash" @selected(old('method') === 'GCash')>GCash (09214309753)</option>
                    <option value="InstaPay" @selected(old('method') === 'InstaPay')>InstaPay (QR Ph Code)</option>
                    <option value="Bank Transfer" @selected(old('method') === 'Bank Transfer')>Bank Transfer</option>
                    <option value="Cash / In-person" @selected(old('method') === 'Cash / In-person')>Cash / Parish Office</option>
                </select>
                <small class="field-hint">Channel used to send the donation.</small>
                @error('method')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="form-field">
                <span class="field-label">Reference / Transaction Number</span>
                <input
                    type="text"
                    name="payment_reference"
                    value="{{ old('payment_reference') }}"
                    placeholder="e.g. 1048 2938 1049 (GCash Ref #)"
                />
                <small class="field-hint">Found on your e-wallet or banking confirmation receipt.</small>
                @error('payment_reference')<span class="field-error">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="section-divider"></div>

        <!-- Section 3: Proof of Payment & Notes -->
        <div class="form-section-header">
            <span class="step-badge">3</span>
            <div>
                <h3>Upload Proof of Payment &amp; Notes</h3>
                <p>Upload a screenshot or photo of your transaction receipt to facilitate prompt verification.</p>
            </div>
        </div>

        <div class="fields-grid full-width-field">
            <div class="form-field">
                <span class="field-label">Upload Proof of Payment (Max 10MB) <strong class="req">*</strong></span>
                <div class="file-upload-box" id="file-drop-zone">
                    <input
                        type="file"
                        id="proof-input"
                        name="proof_of_payment"
                        accept="image/jpeg,image/png,image/webp,application/pdf"
                        required
                        onchange="handleProofPreview(this)"
                    />
                    <div class="upload-prompt" id="upload-prompt">
                        <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <strong class="upload-title">Choose a file or drag it here</strong>
                        <p class="upload-types">Supports PNG, JPG, JPEG, WEBP or PDF (up to 10MB)</p>
                    </div>
                    <div class="upload-preview" id="upload-preview" style="display: none;">
                        <span class="file-name" id="preview-file-name">filename.png</span>
                        <button type="button" class="btn-remove-file" onclick="resetProofInput()">&times; Remove file</button>
                    </div>
                </div>
                @error('proof_of_payment')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <label class="form-field">
                <span class="field-label">Additional Notes / Prayer Intentions</span>
                <textarea
                    name="notes"
                    rows="3"
                    placeholder="Optional message, special intention, or notes for the parish priests and financial team..."
                >{{ old('notes') }}</textarea>
                <small class="field-hint">Any specific instructions or prayer dedications associated with your gift.</small>
                @error('notes')<span class="field-error">{{ $message }}</span>@enderror
            </label>
        </div>

        <!-- Actions -->
        <div class="form-submit-footer">
            <a href="{{ route('parishioner.donations') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary btn-submit-donation">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>Submit for Verification</span>
            </button>
        </div>
    </form>
</div>

<script>
    function handleProofPreview(input) {
        const prompt = document.getElementById('upload-prompt');
        const preview = document.getElementById('upload-preview');
        const nameEl = document.getElementById('preview-file-name');
        if (input.files && input.files[0]) {
            nameEl.textContent = 'Selected: ' + input.files[0].name + ' (' + (input.files[0].size / 1024 / 1024).toFixed(2) + ' MB)';
            prompt.style.display = 'none';
            preview.style.display = 'flex';
        }
    }

    function resetProofInput() {
        const input = document.getElementById('proof-input');
        const prompt = document.getElementById('upload-prompt');
        const preview = document.getElementById('upload-preview');
        input.value = '';
        prompt.style.display = 'block';
        preview.style.display = 'none';
    }
</script>

<style>
    .page-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        gap: 16px;
    }

    .eyebrow {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--gold, #d6aa3e);
        margin: 0 0 4px;
    }

    .page-heading h2 {
        font-family: var(--font-heading);
        font-size: 24px;
        font-weight: 700;
        color: var(--navy, #062f78);
        margin: 0 0 6px;
    }

    .page-heading p {
        margin: 0;
        font-size: 13px;
        color: var(--muted, #718096);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-outline {
        background: #ffffff;
        color: var(--navy, #062f78);
        border: 1.5px solid #cbd5e1;
    }

    .btn-outline:hover {
        border-color: var(--navy, #062f78);
        background: #f8fafc;
    }

    .btn-secondary {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
    }

    .btn-primary {
        background: var(--navy, #062f78);
        color: #ffffff;
        border: 1px solid var(--navy, #062f78);
        box-shadow: 0 4px 12px rgba(6, 47, 120, 0.2);
    }

    .btn-primary:hover {
        background: #0b45a6;
    }

    .form-container-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 34px 38px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        max-width: 900px;
    }

    .form-section-header {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .payment-channels-hint {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 20px;
        font-size: 12px;
        color: #334155;
    }

    .payment-channels-hint .hint-item strong {
        color: var(--navy, #062f78);
    }

    .payment-channels-hint .hint-item small {
        color: #64748b;
        margin-left: 3px;
    }

    .step-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--navy, #062f78);
        color: #ffffff;
        display: grid;
        place-items: center;
        font-size: 13px;
        font-weight: 800;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .form-section-header h3 {
        margin: 0 0 3px;
        font-size: 16px;
        font-weight: 700;
        color: var(--navy, #062f78);
    }

    .form-section-header p {
        margin: 0;
        font-size: 12px;
        color: #64748b;
    }

    .section-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 28px 0;
    }

    .fields-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    @media (max-width: 768px) {
        .fields-grid {
            grid-template-columns: 1fr;
        }
        .form-container-card {
            padding: 24px 20px;
        }
    }

    .full-width-field {
        grid-template-columns: 1fr;
    }

    .form-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .field-label {
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
    }

    .req {
        color: #dc2626;
    }

    .form-field input,
    .form-field select,
    .form-field textarea {
        padding: 11px 14px;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13.5px;
        font-family: inherit;
        color: #0f172a;
        background: #ffffff;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .form-field input:focus,
    .form-field select:focus,
    .form-field textarea:focus {
        outline: none;
        border-color: var(--blue, #0b58b5);
        box-shadow: 0 0 0 3px rgba(11, 88, 181, 0.12);
    }

    .input-with-prefix {
        display: flex;
        align-items: center;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        overflow: hidden;
    }

    .input-with-prefix:focus-within {
        border-color: var(--blue, #0b58b5);
        box-shadow: 0 0 0 3px rgba(11, 88, 181, 0.12);
    }

    .input-prefix {
        padding: 0 14px;
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 14px;
        border-right: 1px solid #cbd5e1;
        height: 100%;
        display: grid;
        place-items: center;
    }

    .input-with-prefix input {
        border: none;
        border-radius: 0;
        flex: 1;
        box-shadow: none !important;
    }

    .field-hint {
        font-size: 11px;
        color: #64748b;
    }

    .field-error {
        font-size: 11.5px;
        color: #dc2626;
        font-weight: 600;
    }

    /* File upload box */
    .file-upload-box {
        position: relative;
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        padding: 24px;
        background: #f8fafc;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .file-upload-box:hover {
        border-color: var(--blue, #0b58b5);
        background: #f0f7ff;
    }

    .file-upload-box input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 5;
    }

    .upload-prompt {
        color: #64748b;
    }

    .upload-prompt svg {
        color: var(--blue, #0b58b5);
        margin-bottom: 8px;
    }

    .upload-title {
        display: block;
        font-size: 13.5px;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .upload-types {
        margin: 0;
        font-size: 11.5px;
    }

    .upload-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        position: relative;
        z-index: 10;
    }

    .file-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--navy, #062f78);
    }

    .btn-remove-file {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
    }

    .form-submit-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 14px;
        margin-top: 32px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
    }

    .alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 14px 18px;
        border-radius: 8px;
        margin-bottom: 22px;
        font-size: 13px;
    }
</style>
@endsection
