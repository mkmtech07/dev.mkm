@php
    $selectedVariables = old('available_variables', implode("\n", $template->available_variables ?? []));
    if (is_array($selectedVariables)) {
        $selectedVariables = implode("\n", $selectedVariables);
    }
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-1">1. Template Information</h2><p class="small text-secondary mb-0">Name, slug, and template purpose.</p></div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label" for="name">Template name <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $template->name) }}" maxlength="255" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="slug">Slug <span class="text-danger">*</span></label>
                        <input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" type="text" value="{{ old('slug', $template->slug) }}" maxlength="255" placeholder="generated-from-name" required>
                        <div class="form-text">Leave blank when creating to generate it from the name.</div>
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="type">Template type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            @foreach(\App\Models\EmailTemplate::TYPES as $item)
                                <option value="{{ $item }}" @selected(old('type', $template->type ?? 'custom') === $item)>{{ \App\Models\EmailTemplate::label($item) }}</option>
                            @endforeach
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-1">2. Email Subject</h2><p class="small text-secondary mb-0">Variables can be used with braces, such as <code>{site_name}</code>.</p></div>
            <div class="card-body p-4">
                <label class="form-label" for="subject">Subject</label>
                <input class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" type="text" value="{{ old('subject', $template->subject) }}" maxlength="255">
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-1">3. Email Body</h2><p class="small text-secondary mb-0">Write plain text or safe email HTML. Preview does not send email.</p></div>
            <div class="card-body p-4">
                <label class="form-label" for="body">Body <span class="text-danger">*</span></label>
                <textarea class="form-control font-monospace @error('body') is-invalid @enderror" id="body" name="body" rows="16" required>{{ old('body', $template->body) }}</textarea>
                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header"><h2 class="h5 mb-1">4. Available Variables</h2><p class="small text-secondary mb-0">One variable per line, comma-separated, or a JSON array.</p></div>
            <div class="card-body p-4">
                <textarea class="form-control font-monospace @error('available_variables') is-invalid @enderror" id="available_variables" name="available_variables" rows="8">{{ $selectedVariables }}</textarea>
                @error('available_variables')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('available_variables.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4 sticky-xl-top" style="top: 96px;">
            <div class="card-header"><h2 class="h5 mb-1">5. Status</h2><p class="small text-secondary mb-0">Control availability and default-template behavior.</p></div>
            <div class="card-body p-4">
                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" id="status" name="status" type="checkbox" value="1" @checked(old('status', $template->status ?? true))>
                    <label class="form-check-label" for="status">Active</label>
                </div>

                <input name="is_default" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" id="is_default" name="is_default" type="checkbox" value="1" @checked(old('is_default', $template->is_default ?? false))>
                    <label class="form-check-label" for="is_default">Default template</label>
                </div>
            </div>
            <div class="card-footer bg-white d-flex gap-2 py-3">
                <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
                <a class="btn btn-light" href="{{ route('admin.email-templates.index') }}">Cancel</a>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header"><h2 class="h5 mb-1">Variable Helper</h2><p class="small text-secondary mb-0">Click to copy a placeholder.</p></div>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($variables as $variable)
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-copy-variable="{{ '{'.$variable.'}' }}">{{ '{'.$variable.'}' }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('[data-copy-variable]').forEach((button) => {
            button.addEventListener('click', async () => {
                const value = button.dataset.copyVariable.replaceAll(' ', '');
                try {
                    await navigator.clipboard.writeText(value);
                    button.classList.replace('btn-outline-secondary', 'btn-outline-success');
                    window.setTimeout(() => button.classList.replace('btn-outline-success', 'btn-outline-secondary'), 900);
                } catch {
                    const body = document.getElementById('body');
                    body?.setRangeText(value, body.selectionStart, body.selectionEnd, 'end');
                    body?.focus();
                }
            });
        });
    </script>
@endpush
