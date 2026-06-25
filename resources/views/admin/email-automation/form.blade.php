@php
    $canEditAutomation = auth()->user()->hasPermission('email_automation.edit');

    $sections = [
        ['1. Contact Emails', [
            ['contact_auto_reply', 'Contact form auto reply'],
            ['contact_admin_alert', 'Contact form admin alert'],
        ]],
        ['2. Lead Emails', [
            ['lead_auto_reply', 'Lead/enquiry customer confirmation'],
            ['lead_admin_alert', 'Lead/enquiry admin alert'],
        ]],
        ['3. Newsletter Emails', [
            ['newsletter_welcome', 'Newsletter welcome email'],
        ]],
        ['4. Backup Alerts', [
            ['backup_success_alert', 'Backup success alert'],
            ['backup_failed_alert', 'Backup failed alert'],
        ]],
        ['5. Maintenance Alerts', [
            ['maintenance_alert', 'Maintenance mode alert'],
        ]],
    ];
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        @foreach($sections as [$title, $items])
            <div class="card content-card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-1">{{ $title }}</h2>
                    <p class="small text-secondary mb-0">Toggle event emails and confirm the required template is available.</p>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        @foreach($items as [$field, $label])
                            @php($templateStatus = $templateStatuses[$field] ?? null)
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 border rounded p-3">
                                <div>
                                    <input name="{{ $field }}" type="hidden" value="0">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="checkbox" value="1" @checked(old($field, $automationSetting->{$field})) @disabled(! $canEditAutomation)>
                                        <label class="form-check-label fw-semibold" for="{{ $field }}">{{ $label }}</label>
                                        @error($field)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="small text-secondary mt-1">Template: <code>{{ $templateStatus['template'] ?? $field }}</code></div>
                                </div>
                                <div class="text-md-end">
                                    @if($templateStatus && $templateStatus['available'])
                                        <span class="badge text-bg-success">Template available</span>
                                        <div class="small text-secondary mt-1"><code>{{ $templateStatus['slug'] }}</code></div>
                                    @else
                                        <span class="badge text-bg-warning">Template missing</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4 sticky-xl-top" style="top: 96px;">
            <div class="card-header">
                <h2 class="h5 mb-1">8. Status</h2>
                <p class="small text-secondary mb-0">Master switch for all automated emails.</p>
            </div>
            <div class="card-body p-4">
                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input @error('status') is-invalid @enderror" id="status" name="status" type="checkbox" value="1" @checked(old('status', $automationSetting->status)) @disabled(! $canEditAutomation)>
                    <label class="form-check-label" for="status">Enable email automation</label>
                    @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-5">SMTP</dt>
                    <dd class="col-7">{{ $mailSetting?->isConfigured() ? 'Active' : 'Inactive / Missing' }}</dd>
                    <dt class="col-5">Mailer</dt>
                    <dd class="col-7">{{ $mailSetting?->mailer ? \App\Models\MailSetting::label($mailSetting->mailer) : 'Not configured' }}</dd>
                    <dt class="col-5">Updated</dt>
                    <dd class="col-7 mb-0">{{ $automationSetting->updated_at?->format('M d, Y h:i A') ?: 'Never' }}</dd>
                </dl>
            </div>
            <div class="card-footer bg-white d-grid py-3">
                <button class="btn btn-primary btn-lg" type="submit" @disabled(! $canEditAutomation)>Save automation settings</button>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">6. Admin Recipient Settings</h2>
                <p class="small text-secondary mb-0">Fallback uses mail sender email, then website email.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="admin_email">Admin notification email</label>
                    <input class="form-control @error('admin_email') is-invalid @enderror" id="admin_email" name="admin_email" type="email" value="{{ old('admin_email', $automationSetting->admin_email) }}" maxlength="255" @disabled(! $canEditAutomation)>
                    @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="cc_email">CC email</label>
                    <input class="form-control @error('cc_email') is-invalid @enderror" id="cc_email" name="cc_email" type="email" value="{{ old('cc_email', $automationSetting->cc_email) }}" maxlength="255" @disabled(! $canEditAutomation)>
                    @error('cc_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="bcc_email">BCC email</label>
                    <input class="form-control @error('bcc_email') is-invalid @enderror" id="bcc_email" name="bcc_email" type="email" value="{{ old('bcc_email', $automationSetting->bcc_email) }}" maxlength="255" @disabled(! $canEditAutomation)>
                    @error('bcc_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">7. Queue Settings</h2>
                <p class="small text-secondary mb-0">Unsafe queue drivers fall back to synchronous sending.</p>
            </div>
            <div class="card-body p-4">
                <input name="queue_emails" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input @error('queue_emails') is-invalid @enderror" id="queue_emails" name="queue_emails" type="checkbox" value="1" @checked(old('queue_emails', $automationSetting->queue_emails)) @disabled(! $canEditAutomation)>
                    <label class="form-check-label" for="queue_emails">Enable queue emails</label>
                    @error('queue_emails')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
