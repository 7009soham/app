@extends('admin.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="page-header">
    <h1>Notifications</h1>
    <p>Configure your website settings</p>
</div>

<div class="settings-layout">
    @include('admin.settings.partials.nav', ['active' => 'notifications'])

    <div class="settings-content">
        <form id="notifications-settings-form" action="{{ route('admin.settings.notifications.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

                <div id="notifications">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-bell"></i> Notifications Settings</h3>
                        </div>
                        <div class="card-body">
                            @php $notificationSettings = $settings->get('notifications', collect()); @endphp
    
                            <div class="form-group" style="padding: 16px; background: #ecfeff; border: 1px solid #a5f3fc; border-radius: 8px; margin-bottom: 20px;">
                                <label style="color: #0e7490; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">
                                    <i class="fas fa-envelope-open-text"></i> Advance Due Reminder Emails
                                </label>
                                <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                    Send reminder email before due date: "Please make your payment before X date to avoid penalty".
                                </p>
    
                                <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: center;">
                                    <label for="due_reminder_enabled" style="display:flex; align-items:center; gap:8px; margin:0; cursor:pointer;">
                                        <input type="checkbox" id="due_reminder_enabled" name="due_reminder_enabled" value="1"
                                            @if(($notificationSettings->firstWhere('key', 'due_reminder_enabled')?->value ?? '1') == '1') checked @endif>
                                        <span>Enable reminders</span>
                                    </label>
    
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <label for="due_reminder_days_before" style="margin:0; color:#0f172a; font-weight:500;">Days before due date</label>
                                        <input type="number" id="due_reminder_days_before" name="due_reminder_days_before" class="form-control"
                                               value="{{ $notificationSettings->firstWhere('key', 'due_reminder_days_before')?->value ?? '3' }}"
                                               min="0" max="30" step="1" style="width:90px;">
                                    </div>
                                </div>
                                <small style="color: #64748b; margin-top: 8px; display: block;">
                                    Configure the scheduler to run daily so reminders are sent on time.
                                </small>
                            </div>
    
                            <div class="form-group" style="padding: 16px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 20px;">
                                <label style="color: #0f172a; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">
                                    <i class="fas fa-envelope"></i> SMTP Email Delivery (Invoices, Confirmation, Due Reminders)
                                </label>
                                <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                    Configure SMTP so payment confirmation, invoice emails, and due-date reminder emails are sent from this system.
                                </p>
    
                                <label for="smtp_enabled" style="display:flex; align-items:center; gap:8px; margin:0 0 14px; cursor:pointer;">
                                    <input type="checkbox" id="smtp_enabled" name="smtp_enabled" value="1"
                                        @if(($notificationSettings->firstWhere('key', 'smtp_enabled')?->value ?? '0') == '1') checked @endif>
                                    <span>Enable SMTP email sending</span>
                                </label>
    
                                <div id="smtp_options" style="display: {{ ($notificationSettings->firstWhere('key', 'smtp_enabled')?->value ?? '0') == '1' ? 'block' : 'none' }};">
                                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="smtp_host">SMTP Host</label>
                                            <input type="text" id="smtp_host" name="smtp_host" class="form-control"
                                                   value="{{ $notificationSettings->firstWhere('key', 'smtp_host')?->value ?? '' }}"
                                                   placeholder="smtp.gmail.com">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="smtp_port">Port</label>
                                            <input type="number" id="smtp_port" name="smtp_port" class="form-control"
                                                   value="{{ $notificationSettings->firstWhere('key', 'smtp_port')?->value ?? '587' }}"
                                                   min="1" max="65535" step="1">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="smtp_encryption">Encryption</label>
                                            @php $smtpEncryption = $notificationSettings->firstWhere('key', 'smtp_encryption')?->value ?? 'tls'; @endphp
                                            <select id="smtp_encryption" name="smtp_encryption" class="form-control">
                                                <option value="tls" {{ $smtpEncryption === 'tls' ? 'selected' : '' }}>TLS</option>
                                                <option value="ssl" {{ $smtpEncryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                                                <option value="none" {{ $smtpEncryption === 'none' ? 'selected' : '' }}>None</option>
                                            </select>
                                        </div>
                                    </div>
    
                                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="smtp_username">SMTP Username</label>
                                            <input type="text" id="smtp_username" name="smtp_username" class="form-control"
                                                   value="{{ $notificationSettings->firstWhere('key', 'smtp_username')?->value ?? '' }}"
                                                   placeholder="sender@example.com">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="smtp_password">SMTP Password</label>
                                            <input type="password" id="smtp_password" name="smtp_password" class="form-control"
                                                   value="" placeholder="Enter password or app password">
                                            <small style="color: #64748b;">Leave blank to keep existing password.</small>
                                        </div>
                                    </div>
    
                                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-top: 14px;">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="smtp_from_address">From Email Address</label>
                                            <input type="email" id="smtp_from_address" name="smtp_from_address" class="form-control"
                                                   value="{{ $notificationSettings->firstWhere('key', 'smtp_from_address')?->value ?? '' }}"
                                                   placeholder="sender@example.com">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="smtp_from_name">From Name</label>
                                            <input type="text" id="smtp_from_name" name="smtp_from_name" class="form-control"
                                                   value="{{ $notificationSettings->firstWhere('key', 'smtp_from_name')?->value ?? '' }}"
                                                   placeholder="Neral Gram Panchayat">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="smtp_timeout">Timeout (seconds)</label>
                                            <input type="number" id="smtp_timeout" name="smtp_timeout" class="form-control"
                                                   value="{{ $notificationSettings->firstWhere('key', 'smtp_timeout')?->value ?? '30' }}"
                                                   min="5" max="300" step="1">
                                        </div>
                                    </div>
                                </div>
    
                                <script>
                                    document.getElementById('smtp_enabled').addEventListener('change', function() {
                                        document.getElementById('smtp_options').style.display = this.checked ? 'block' : 'none';
                                    });
                                </script>
                            </div>
    
                            <div class="form-group" style="padding: 16px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; margin-bottom: 20px;">
                                <label style="color: #166534; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">
                                    <i class="fas fa-user-check"></i> Email Verification
                                </label>
                                <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                    Control whether citizens must verify email using OTP. If disabled, email is marked verified directly in backend.
                                </p>
    
                                <label for="email_verification_enabled" style="display:flex; align-items:center; gap:8px; margin:0; cursor:pointer;">
                                    <input type="checkbox" id="email_verification_enabled" name="email_verification_enabled" value="1"
                                        @if(($notificationSettings->firstWhere('key', 'email_verification_enabled')?->value ?? '1') == '1') checked @endif>
                                    <span>Enable Email Verification</span>
                                </label>
                            </div>
    
                            @php
                                $emailTemplateDefinitions = [
                                    [
                                        'title' => 'Invoice Generation Email',
                                        'mode_key' => 'email_template_invoice_mode',
                                        'subject_key' => 'email_template_invoice_subject',
                                        'body_key' => 'email_template_invoice_body',
                                        'placeholder_hint' => '{{user_name}}, {{invoice_number}}, {{invoice_date}}, {{billing_period}}, {{due_date}}, {{amount_due}}, {{details_table}}, {{invoice_line_items}}',
                                    ],
                                    [
                                        'title' => 'Due Date Reminder Email',
                                        'mode_key' => 'email_template_due_reminder_mode',
                                        'subject_key' => 'email_template_due_reminder_subject',
                                        'body_key' => 'email_template_due_reminder_body',
                                        'placeholder_hint' => '{{user_name}}, {{customer_no}}, {{bill_no}}, {{billing_period}}, {{due_date}}, {{amount_due}}, {{details_table}}',
                                    ],
                                    [
                                        'title' => 'Payment Success Email',
                                        'mode_key' => 'email_template_payment_success_mode',
                                        'subject_key' => 'email_template_payment_success_subject',
                                        'body_key' => 'email_template_payment_success_body',
                                        'placeholder_hint' => '{{user_name}}, {{transaction_id}}, {{payment_date}}, {{tax_amount}}, {{convenience_fee}}, {{total_amount}}, {{details_table}}',
                                    ],
                                    [
                                        'title' => 'Payment Failure Email',
                                        'mode_key' => 'email_template_payment_failure_mode',
                                        'subject_key' => 'email_template_payment_failure_subject',
                                        'body_key' => 'email_template_payment_failure_body',
                                        'placeholder_hint' => '{{user_name}}, {{transaction_id}}, {{attempted_at}}, {{total_amount}}, {{failure_reason}}, {{next_step}}, {{details_table}}',
                                    ],
                                    [
                                        'title' => 'Completion / Status Notification Email',
                                        'mode_key' => 'email_template_completion_mode',
                                        'subject_key' => 'email_template_completion_subject',
                                        'body_key' => 'email_template_completion_body',
                                        'placeholder_hint' => '{{user_name}}, {{ticket_number}}, {{service_name}}, {{previous_status}}, {{current_status}}, {{updated_at}}, {{summary}}, {{admin_remarks}}, {{details_table}}',
                                    ],
                                ];
                            @endphp
    
                            <div class="form-group" style="padding: 16px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 20px;">
                                <label style="color: #0f172a; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">
                                    <i class="fas fa-pen-nib"></i> Email Content Templates (Rich Text Editor)
                                </label>
                                <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                    Configure custom email subject and body content using the editor. The system converts content to clean HTML, injects dynamic placeholders, and applies the fixed professional email layout (header, footer, and styling).
                                </p>
    
                                <div class="email-template-group">
                                    <div class="form-row" style="max-width: 460px; margin-bottom: 10px;">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="email-template-selector">Select Email Type</label>
                                            <select id="email-template-selector" class="form-control">
                                                @foreach($emailTemplateDefinitions as $template)
                                                    <option value="{{ $template['body_key'] }}" {{ $loop->first ? 'selected' : '' }}>
                                                        {{ $template['title'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="email-template-help" style="margin-top: 6px;">
                                                Choose one email type from the dropdown to edit subject and body.
                                            </small>
                                        </div>
                                    </div>
    
                                    @foreach($emailTemplateDefinitions as $template)
                                        @php
                                            $subjectValue = $notificationSettings->firstWhere('key', $template['subject_key'])?->value ?? '';
                                            $bodyValue = $notificationSettings->firstWhere('key', $template['body_key'])?->value ?? '';
                                            $modeValue = $notificationSettings->firstWhere('key', $template['mode_key'])?->value ?? (trim((string) $bodyValue) === '' ? 'current' : 'custom');
                                        @endphp
    
                                        <div class="email-template-card {{ $loop->first ? '' : 'is-hidden' }}" data-template-card="{{ $template['body_key'] }}">
                                            <h4>{{ $template['title'] }}</h4>
    
                                            <div class="form-group" style="margin-bottom: 12px;">
                                                <label for="{{ $template['mode_key'] }}">Template Mode</label>
                                                <select id="{{ $template['mode_key'] }}"
                                                        name="{{ $template['mode_key'] }}"
                                                        class="form-control email-template-mode-select"
                                                        data-custom-fields-target="{{ $template['body_key'] }}_custom_fields">
                                                    <option value="current" {{ $modeValue === 'current' ? 'selected' : '' }}>Use current system template (currently used)</option>
                                                    <option value="custom" {{ $modeValue === 'custom' ? 'selected' : '' }}>Use custom rich text template</option>
                                                </select>
                                                <small class="email-template-help" style="margin-top: 6px;">
                                                    Current system template uses the existing built-in email layout. Custom mode uses the editor content with the fixed professional wrapper.
                                                </small>
                                            </div>
    
                                            <div id="{{ $template['body_key'] }}_custom_fields" class="custom-template-fields {{ $modeValue === 'custom' ? '' : 'is-hidden' }}">
    
                                            <div class="form-group" style="margin-bottom: 12px;">
                                                <label for="{{ $template['subject_key'] }}">Email Subject</label>
                                                <input type="text"
                                                       id="{{ $template['subject_key'] }}"
                                                       name="{{ $template['subject_key'] }}"
                                                       class="form-control"
                                                       value="{{ old($template['subject_key'], $subjectValue) }}"
                                                       placeholder="Enter subject line">
                                            </div>
    
                                            <div class="form-group" style="margin-bottom: 10px;">
                                                <label for="{{ $template['body_key'] }}_editor">Email Body</label>
                                                <textarea class="email-template-source"
                                                          style="display:none;"
                                                          name="{{ $template['body_key'] }}"
                                                          id="{{ $template['body_key'] }}">{!! old($template['body_key'], $bodyValue) !!}</textarea>
                                                <div id="{{ $template['body_key'] }}_editor"
                                                     class="email-template-editor"
                                                     data-source-input="{{ $template['body_key'] }}"></div>
                                            </div>
    
                                            <small class="email-template-help">
                                                Placeholders: {{ $template['placeholder_hint'] }}
                                            </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="d-flex gap-3 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Notifications
                </button>
            </div>
        </form>
    
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-paper-plane"></i> System Email Simulation (Admin Test)</h3>
                </div>
                <div class="card-body">
                    <p style="margin:0 0 14px;color:#475569;">
                        Trigger invoice generation, due reminder, payment status (success and failure), and completion/status notifications for preview and testing.
                    </p>
    
                    <form action="{{ route('admin.settings.simulate-emails') }}" method="POST">
                        @csrf
    
                        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-group">
                                <label for="simulation_email">Recipient Email</label>
                                <input
                                    type="email"
                                    id="simulation_email"
                                    name="simulation_email"
                                    class="form-control"
                                    value="{{ old('simulation_email', 'soham.tare@somaiya.edu') }}"
                                    placeholder="soham.tare@somaiya.edu"
                                    required
                                >
                                @error('simulation_email')
                                    <small style="color:#dc2626;">{{ $message }}</small>
                                @enderror
                            </div>
    
                            <div class="form-group">
                                <label for="simulation_name">Recipient Name</label>
                                <input
                                    type="text"
                                    id="simulation_name"
                                    name="simulation_name"
                                    class="form-control"
                                    value="{{ old('simulation_name', 'Soham Tare') }}"
                                    placeholder="Soham Tare"
                                >
                            </div>
                        </div>
    
                        <div class="form-group" style="margin-top:4px;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" name="preview_only" value="1" {{ old('preview_only') ? 'checked' : '' }}>
                                <span>Preview only (generate HTML previews without sending emails)</span>
                            </label>
                        </div>
    
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play-circle"></i> Run Email Simulation
                        </button>
                    </form>
                </div>
            </div>
    
            @if(session('email_simulation_result'))
                @php $simulationResult = session('email_simulation_result'); @endphp
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><i class="fas fa-envelope-open-text"></i> Latest Simulation Result</h3>
                    </div>
                    <div class="card-body">
                        <p style="margin:0 0 6px;"><strong>Recipient:</strong> {{ $simulationResult['recipient_name'] ?? '-' }} ({{ $simulationResult['recipient_email'] ?? '-' }})</p>
                        <p style="margin:0 0 6px;"><strong>Mode:</strong> {{ $simulationResult['send_mode'] ?? '-' }}</p>
                        <p style="margin:0 0 6px;"><strong>Preview Directory:</strong> {{ $simulationResult['preview_directory'] ?? '-' }}</p>
                        <p style="margin:0 0 14px;"><strong>Summary:</strong> Total {{ $simulationResult['summary']['total'] ?? 0 }}, Sent {{ $simulationResult['summary']['sent'] ?? 0 }}, Failed {{ $simulationResult['summary']['failed'] ?? 0 }}</p>
    
                        <div style="overflow:auto;">
                            <table style="width:100%;border-collapse:collapse;min-width:720px;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left;padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Email Type</th>
                                        <th style="text-align:left;padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Subject</th>
                                        <th style="text-align:left;padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Status</th>
                                        <th style="text-align:left;padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Preview</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($simulationResult['emails'] ?? []) as $emailResult)
                                        <tr>
                                            <td style="padding:10px;border:1px solid #e2e8f0;">{{ $emailResult['label'] ?? '-' }}</td>
                                            <td style="padding:10px;border:1px solid #e2e8f0;">{{ $emailResult['subject'] ?? '-' }}</td>
                                            <td style="padding:10px;border:1px solid #e2e8f0;">
                                                <span style="font-weight:600;">{{ strtoupper($emailResult['status'] ?? '-') }}</span>
                                                @if(!empty($emailResult['error']))
                                                    <div style="margin-top:4px;color:#dc2626;font-size:12px;">{{ $emailResult['error'] }}</div>
                                                @endif
                                            </td>
                                            <td style="padding:10px;border:1px solid #e2e8f0;">
                                                @if(!empty($emailResult['preview_url']))
                                                    <a href="{{ $emailResult['preview_url'] }}" target="_blank" rel="noopener">Open preview</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
    </div>
</div>
@endsection


@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
@include('admin.settings.partials.styles')
<style>
    .email-template-group {
        display: grid;
        gap: 14px;
    }

    .email-template-card {
        border: 1px solid #dbe3ef;
        border-radius: 10px;
        background: #ffffff;
        padding: 14px;
    }

    .email-template-card.is-hidden {
        display: none;
    }

    .custom-template-fields.is-hidden {
        display: none;
    }

    .email-template-card h4 {
        margin: 0 0 12px;
        font-size: 15px;
        color: #1e293b;
    }

    .email-template-editor {
        background: #ffffff;
        border-radius: 8px;
    }

    .email-template-editor .ql-editor {
        min-height: 150px;
        font-size: 14px;
        line-height: 1.6;
    }

    .email-template-help {
        display: block;
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    const settingsForm = document.getElementById('notifications-settings-form');
    if (settingsForm) {
        const templateSelector = document.getElementById('email-template-selector');
        const templateCards = document.querySelectorAll('.email-template-card[data-template-card]');
        const templateModeSelectors = document.querySelectorAll('.email-template-mode-select');

        const showSelectedTemplateCard = function (templateKey) {
            templateCards.forEach(card => {
                const cardKey = card.getAttribute('data-template-card');
                const isMatch = cardKey === templateKey;
                card.classList.toggle('is-hidden', !isMatch);
            });
        };

        if (templateSelector && templateCards.length > 0) {
            showSelectedTemplateCard(templateSelector.value || templateCards[0].getAttribute('data-template-card'));
            templateSelector.addEventListener('change', function () {
                showSelectedTemplateCard(this.value);
            });
        }

        const syncCustomFieldsVisibility = function (modeSelectElement) {
            const targetId = modeSelectElement.dataset.customFieldsTarget;
            const targetElement = targetId ? document.getElementById(targetId) : null;
            if (!targetElement) {
                return;
            }

            targetElement.classList.toggle('is-hidden', modeSelectElement.value !== 'custom');
        };

        templateModeSelectors.forEach(modeSelectElement => {
            syncCustomFieldsVisibility(modeSelectElement);
            modeSelectElement.addEventListener('change', function () {
                syncCustomFieldsVisibility(this);
            });
        });

        const sourceTextareas = document.querySelectorAll('.email-template-source');

        if (typeof Quill === 'undefined') {
            sourceTextareas.forEach(textarea => {
                textarea.style.display = 'block';
                textarea.rows = 8;
                textarea.classList.add('form-control');
            });
        } else {
            const editors = [];

            document.querySelectorAll('.email-template-editor').forEach(editorElement => {
                const sourceId = editorElement.dataset.sourceInput;
                const sourceTextarea = document.getElementById(sourceId);

                if (!sourceTextarea) {
                    return;
                }

                const quill = new Quill(editorElement, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            [{ align: [] }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                quill.root.innerHTML = sourceTextarea.value || '<p><br></p>';
                editors.push({ quill, sourceTextarea });
            });

            settingsForm.addEventListener('submit', function () {
                editors.forEach(({ quill, sourceTextarea }) => {
                    sourceTextarea.value = quill.root.innerHTML;
                });
            });
        }
    }
</script>
@endpush
