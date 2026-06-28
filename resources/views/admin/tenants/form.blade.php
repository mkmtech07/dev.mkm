@php
    $selectedModules = old('allowed_modules', $tenant->allowed_modules ?? array_keys($moduleOptions));
    $selectedModules = is_array($selectedModules) ? $selectedModules : [];
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Tenant identity</h2>
                <p class="text-secondary small mb-0">Set the demo name, URL handle, and client contact details.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label" for="name">Tenant name <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $tenant->name) }}" maxlength="255" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="slug">Slug <span class="text-danger">*</span></label>
                        <input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" type="text" value="{{ old('slug', $tenant->slug) }}" maxlength="120" @readonly($tenant->isDefault()) required>
                        @if($tenant->isDefault())
                            <div class="form-text">The default tenant slug is locked.</div>
                        @endif
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="subdomain">Subdomain</label>
                        <input class="form-control @error('subdomain') is-invalid @enderror" id="subdomain" name="subdomain" type="text" value="{{ old('subdomain', $tenant->subdomain) }}" maxlength="120" placeholder="client-name">
                        <div class="form-text">Example: client-name.yourdomain.com</div>
                        @error('subdomain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="custom_domain">Custom domain</label>
                        <input class="form-control @error('custom_domain') is-invalid @enderror" id="custom_domain" name="custom_domain" type="text" value="{{ old('custom_domain', $tenant->custom_domain) }}" maxlength="255" placeholder="demo.client.com">
                        <div class="form-text">Enter only the host name, without https://.</div>
                        @error('custom_domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="alert alert-info mb-4">
                    Point subdomains or custom domains to this Laravel app. Host resolution happens automatically after DNS reaches the server.
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="client_name">Client name</label>
                        <input class="form-control @error('client_name') is-invalid @enderror" id="client_name" name="client_name" type="text" value="{{ old('client_name', $tenant->client_name) }}" maxlength="255">
                        @error('client_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="client_email">Client email</label>
                        <input class="form-control @error('client_email') is-invalid @enderror" id="client_email" name="client_email" type="email" value="{{ old('client_email', $tenant->client_email) }}" maxlength="255">
                        @error('client_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="client_phone">Client phone</label>
                        <input class="form-control @error('client_phone') is-invalid @enderror" id="client_phone" name="client_phone" type="text" value="{{ old('client_phone', $tenant->client_phone) }}" maxlength="50">
                        @error('client_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">Allowed modules</h2>
                <p class="text-secondary small mb-0">Track which CMS areas are expected for this client demo.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-2">
                    @foreach($moduleOptions as $value => $label)
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-check">
                                <input class="form-check-input" id="module_{{ $value }}" name="allowed_modules[]" type="checkbox" value="{{ $value }}" @checked(in_array($value, $selectedModules, true))>
                                <label class="form-check-label" for="module_{{ $value }}">{{ $label }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('allowed_modules') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Status</h2>
                <p class="text-secondary small mb-0">Control public availability for this tenant.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" @disabled($tenant->isDefault()) required>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $tenant->status ?? \App\Models\Tenant::STATUS_ACTIVE) === $status)>{{ str($status)->title() }}</option>
                        @endforeach
                    </select>
                    @if($tenant->isDefault())
                        <input name="status" type="hidden" value="{{ \App\Models\Tenant::STATUS_ACTIVE }}">
                    @endif
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <input name="is_demo" type="hidden" value="0">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" id="is_demo" name="is_demo" type="checkbox" value="1" @checked(old('is_demo', $tenant->is_demo ?? true)) @disabled($tenant->isDefault())>
                    <label class="form-check-label" for="is_demo">Demo tenant</label>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="demo_expires_at">Demo expires at</label>
                    <input class="form-control @error('demo_expires_at') is-invalid @enderror" id="demo_expires_at" name="demo_expires_at" type="datetime-local" value="{{ old('demo_expires_at', $tenant->demo_expires_at?->format('Y-m-d\TH:i')) }}" @disabled($tenant->isDefault())>
                    @error('demo_expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="notes">Internal notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="5" maxlength="10000">{{ old('notes', $tenant->notes) }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ $tenant->exists ? route('admin.tenants.show', $tenant) : route('admin.tenants.index') }}">Cancel</a>
        </div>
    </div>
</div>
