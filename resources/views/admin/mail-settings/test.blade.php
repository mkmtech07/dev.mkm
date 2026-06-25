@php
    $canTestMail = auth()->user()->hasPermission('mail_settings.test');
@endphp

<div class="card content-card mt-4">
    <div class="card-header">
        <h2 class="h5 mb-1">Send Test Email</h2>
        <p class="small text-secondary mb-0">Uses the current saved database mail settings.</p>
    </div>
    <div class="card-body p-4">
        <form class="row g-3 align-items-end" method="POST" action="{{ route('admin.mail-settings.test') }}">
            @csrf
            <div class="col-md-8 col-xl-5">
                <label class="form-label" for="send_test_recipient">Recipient <span class="text-danger">*</span></label>
                <input class="form-control @error('test_recipient') is-invalid @enderror" id="send_test_recipient" name="test_recipient" type="email" value="{{ old('test_recipient', $mailSetting->test_recipient) }}" maxlength="255" required @disabled(! $canTestMail)>
                @error('test_recipient')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-auto">
                <button class="btn btn-outline-primary" type="submit" @disabled(! $canTestMail)>Send test email</button>
            </div>
            @unless($mailSetting->isConfigured())
                <div class="col-12">
                    <div class="small text-warning-emphasis">Mail settings are not configured, so this test will fail until a usable driver is saved.</div>
                </div>
            @endunless
        </form>
    </div>
</div>
