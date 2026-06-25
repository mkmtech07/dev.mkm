@php
    $canEditMailSettings = auth()->user()->hasPermission('mail_settings.edit');
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">1. Mail Driver</h2>
                <p class="small text-secondary mb-0">Choose the Laravel mail transport used by database mail settings.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="mailer">Mailer <span class="text-danger">*</span></label>
                        <select class="form-select @error('mailer') is-invalid @enderror" id="mailer" name="mailer" required @disabled(! $canEditMailSettings)>
                            @foreach(\App\Models\MailSetting::MAILERS as $mailer)
                                <option value="{{ $mailer }}" @selected(old('mailer', $mailSetting->mailer) === $mailer)>{{ \App\Models\MailSetting::label($mailer) }}</option>
                            @endforeach
                        </select>
                        @error('mailer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="timeout">Timeout</label>
                        <input class="form-control @error('timeout') is-invalid @enderror" id="timeout" name="timeout" type="number" min="5" max="120" value="{{ old('timeout', $mailSetting->timeout ?? 30) }}" @disabled(! $canEditMailSettings)>
                        <div class="form-text">Seconds. Allowed range: 5 to 120.</div>
                        @error('timeout')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">2. SMTP Server</h2>
                <p class="small text-secondary mb-0">Used only when the SMTP mailer is selected.</p>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info small mb-4" role="alert">
                    SMTP password is encrypted and will not be shown after saving.
                </div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="host">SMTP host</label>
                        <input class="form-control @error('host') is-invalid @enderror" id="host" name="host" type="text" value="{{ old('host', $mailSetting->host) }}" maxlength="255" placeholder="smtp.example.com" @disabled(! $canEditMailSettings)>
                        @error('host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="port">SMTP port</label>
                        <input class="form-control @error('port') is-invalid @enderror" id="port" name="port" type="number" min="1" max="65535" value="{{ old('port', $mailSetting->port) }}" placeholder="587" @disabled(! $canEditMailSettings)>
                        @error('port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="username">SMTP username</label>
                        <input class="form-control @error('username') is-invalid @enderror" id="username" name="username" type="text" value="{{ old('username', $mailSetting->username) }}" maxlength="255" autocomplete="off" @disabled(! $canEditMailSettings)>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">SMTP password</label>
                        <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" value="{{ old('password') }}" maxlength="255" autocomplete="new-password" placeholder="{{ $mailSetting->hasStoredPassword() ? 'Password already saved. Leave blank to keep existing password.' : 'Enter SMTP password' }}" @disabled(! $canEditMailSettings)>
                        <div class="form-text">{{ $mailSetting->hasStoredPassword() ? 'Leave blank to keep the existing encrypted password.' : 'The password will be encrypted before storage.' }}</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="encryption">Encryption</label>
                        <select class="form-select @error('encryption') is-invalid @enderror" id="encryption" name="encryption" @disabled(! $canEditMailSettings)>
                            <option value="">Default</option>
                            @foreach(\App\Models\MailSetting::ENCRYPTIONS as $encryption)
                                <option value="{{ $encryption }}" @selected(old('encryption', $mailSetting->encryption) === $encryption)>{{ \App\Models\MailSetting::label($encryption) }}</option>
                            @endforeach
                        </select>
                        @error('encryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">3. Sender Information</h2>
                <p class="small text-secondary mb-0">Default identity used for outgoing website emails.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="from_address">From email address</label>
                        <input class="form-control @error('from_address') is-invalid @enderror" id="from_address" name="from_address" type="email" value="{{ old('from_address', $mailSetting->from_address) }}" maxlength="255" placeholder="hello@example.com" @disabled(! $canEditMailSettings)>
                        @error('from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="from_name">From name</label>
                        <input class="form-control @error('from_name') is-invalid @enderror" id="from_name" name="from_name" type="text" value="{{ old('from_name', $mailSetting->from_name) }}" maxlength="255" @disabled(! $canEditMailSettings)>
                        @error('from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">4. Reply-To Information</h2>
                <p class="small text-secondary mb-0">Optional reply destination for customer responses.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="reply_to_address">Reply-to email address</label>
                        <input class="form-control @error('reply_to_address') is-invalid @enderror" id="reply_to_address" name="reply_to_address" type="email" value="{{ old('reply_to_address', $mailSetting->reply_to_address) }}" maxlength="255" @disabled(! $canEditMailSettings)>
                        @error('reply_to_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="reply_to_name">Reply-to name</label>
                        <input class="form-control @error('reply_to_name') is-invalid @enderror" id="reply_to_name" name="reply_to_name" type="text" value="{{ old('reply_to_name', $mailSetting->reply_to_name) }}" maxlength="255" @disabled(! $canEditMailSettings)>
                        @error('reply_to_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">5. Test Email</h2>
                <p class="small text-secondary mb-0">Save the default test recipient used by the send test action.</p>
            </div>
            <div class="card-body p-4">
                <label class="form-label" for="test_recipient">Test recipient email</label>
                <input class="form-control @error('test_recipient') is-invalid @enderror" id="test_recipient" name="test_recipient" type="email" value="{{ old('test_recipient', $mailSetting->test_recipient) }}" maxlength="255" placeholder="admin@example.com" @disabled(! $canEditMailSettings)>
                @error('test_recipient')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card sticky-xl-top" style="top: 96px;">
            <div class="card-header">
                <h2 class="h5 mb-1">6. Status</h2>
                <p class="small text-secondary mb-0">Only the first mail settings row is used.</p>
            </div>
            <div class="card-body p-4">
                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input @error('status') is-invalid @enderror" id="status" name="status" type="checkbox" value="1" @checked(old('status', $mailSetting->status)) @disabled(! $canEditMailSettings)>
                    <label class="form-check-label" for="status">Enable mail sending</label>
                    @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-5">Configured</dt>
                    <dd class="col-7">{{ $mailSetting->isConfigured() ? 'Yes' : 'No' }}</dd>
                    <dt class="col-5">Mailer</dt>
                    <dd class="col-7">{{ \App\Models\MailSetting::label($mailSetting->mailer) }}</dd>
                    <dt class="col-5">Password</dt>
                    <dd class="col-7">{{ $mailSetting->hasStoredPassword() ? 'Saved' : 'Not saved' }}</dd>
                    <dt class="col-5">Updated</dt>
                    <dd class="col-7 mb-0">{{ $mailSetting->updated_at?->format('M d, Y h:i A') ?: 'Never' }}</dd>
                </dl>
            </div>
            <div class="card-footer bg-white d-grid py-3">
                <button class="btn btn-primary btn-lg" type="submit" @disabled(! $canEditMailSettings)>Save mail settings</button>
            </div>
        </div>
    </div>
</div>
