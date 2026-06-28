@php
    $canEditMaintenance = auth()->user()->hasPermission('maintenance.edit');
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">1. Maintenance Status</h2>
                <p class="small text-secondary mb-0">Enable maintenance mode and choose how much public traffic it affects.</p>
            </div>
            <div class="card-body p-4">
                <input name="status" type="hidden" value="0">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label d-block" for="status">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input @error('status') is-invalid @enderror" id="status" name="status" type="checkbox" value="1" @checked(old('status', $maintenanceSetting->status)) @disabled(! $canEditMaintenance)>
                            <label class="form-check-label" for="status">Enable maintenance mode</label>
                            @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="mode">Mode</label>
                        <select class="form-select @error('mode') is-invalid @enderror" id="mode" name="mode" @disabled(! $canEditMaintenance)>
                            <option value="frontend_only" @selected(old('mode', $maintenanceSetting->mode) === 'frontend_only')>Frontend Only</option>
                            <option value="full_site" @selected(old('mode', $maintenanceSetting->mode) === 'full_site')>Full Site</option>
                        </select>
                        @error('mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">2. Maintenance Content</h2>
                <p class="small text-secondary mb-0">Message, visual, and optional action shown to visitors.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="title">Title</label>
                        <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" type="text" value="{{ old('title', $maintenanceSetting->title) }}" maxlength="255" @disabled(! $canEditMaintenance)>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="message">Message</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" @disabled(! $canEditMaintenance)>{{ old('message', $maintenanceSetting->message) }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="image">Maintenance image</label>
                        <div class="bg-light border rounded p-3 mb-2 text-center">
                            <img id="maintenance-image-preview" class="image-preview {{ $maintenanceSetting->image ? '' : 'd-none' }}" @if($maintenanceSetting->image) src="{{ asset($maintenanceSetting->image) }}" @endif alt="Maintenance image preview">
                            <span id="maintenance-image-empty" class="small text-secondary {{ $maintenanceSetting->image ? 'd-none' : '' }}">No image uploaded</span>
                        </div>
                        <input class="form-control @error('image') is-invalid @enderror" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.svg" data-image-input data-preview="maintenance-image-preview" data-empty="maintenance-image-empty" @disabled(! $canEditMaintenance)>
                        <div class="form-text">JPG, PNG, WebP, or SVG. Max 2 MB.</div>
                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if ($canEditMaintenance)
                            @include('admin.components.media-picker', [
                                'inputName' => 'image',
                                'previewUrl' => $maintenanceSetting->image ? asset($maintenanceSetting->image) : null,
                                'label' => 'Maintenance image',
                                'acceptType' => 'image',
                            ])
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="button_text">Button text</label>
                            <input class="form-control @error('button_text') is-invalid @enderror" id="button_text" name="button_text" type="text" value="{{ old('button_text', $maintenanceSetting->button_text) }}" maxlength="255" @disabled(! $canEditMaintenance)>
                            @error('button_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="button_url">Button URL</label>
                            <input class="form-control @error('button_url') is-invalid @enderror" id="button_url" name="button_url" type="text" value="{{ old('button_url', $maintenanceSetting->button_url) }}" maxlength="500" placeholder="/contact" @disabled(! $canEditMaintenance)>
                            @error('button_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">3. Schedule</h2>
                <p class="small text-secondary mb-0">Leave dates empty to start immediately and continue until disabled.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="start_at">Start at</label>
                        <input class="form-control @error('start_at') is-invalid @enderror" id="start_at" name="start_at" type="datetime-local" value="{{ old('start_at', $maintenanceSetting->start_at?->format('Y-m-d\TH:i')) }}" @disabled(! $canEditMaintenance)>
                        @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="end_at">End at</label>
                        <input class="form-control @error('end_at') is-invalid @enderror" id="end_at" name="end_at" type="datetime-local" value="{{ old('end_at', $maintenanceSetting->end_at?->format('Y-m-d\TH:i')) }}" @disabled(! $canEditMaintenance)>
                        @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">4. Access Control</h2>
                <p class="small text-secondary mb-0">Let trusted IPs or selected public paths bypass maintenance mode.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="allowed_ips">Allowed IP addresses</label>
                        <textarea class="form-control font-monospace @error('allowed_ips') is-invalid @enderror" id="allowed_ips" name="allowed_ips" rows="7" placeholder="127.0.0.1&#10;203.0.113.10" @disabled(! $canEditMaintenance)>{{ old('allowed_ips', $maintenanceSetting->allowed_ips) }}</textarea>
                        <div class="form-text">Use comma-separated or one IP per line.</div>
                        @error('allowed_ips')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="excluded_paths">Excluded paths</label>
                        <textarea class="form-control font-monospace @error('excluded_paths') is-invalid @enderror" id="excluded_paths" name="excluded_paths" rows="7" placeholder="/contact&#10;/api/contact&#10;/sitemap.xml" @disabled(! $canEditMaintenance)>{{ old('excluded_paths', $maintenanceSetting->excluded_paths) }}</textarea>
                        <div class="form-text">Admin, login, logout, maintenance API, assets, robots, and sitemap are always excluded.</div>
                        @error('excluded_paths')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">5. SEO &amp; Response</h2>
                <p class="small text-secondary mb-0">Control crawler instructions and the HTTP retry hint.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="retry_after_minutes">Retry-after minutes</label>
                        <input class="form-control @error('retry_after_minutes') is-invalid @enderror" id="retry_after_minutes" name="retry_after_minutes" type="number" min="1" max="10080" value="{{ old('retry_after_minutes', $maintenanceSetting->retry_after_minutes ?? 60) }}" @disabled(! $canEditMaintenance)>
                        @error('retry_after_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="meta_robots">Robots meta</label>
                        <select class="form-select @error('meta_robots') is-invalid @enderror" id="meta_robots" name="meta_robots" @disabled(! $canEditMaintenance)>
                            <option value="noindex" @selected(old('meta_robots', $maintenanceSetting->meta_robots) === 'noindex')>noindex</option>
                            <option value="index" @selected(old('meta_robots', $maintenanceSetting->meta_robots) === 'index')>index</option>
                        </select>
                        @error('meta_robots')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">6. Custom CSS</h2>
                <p class="small text-secondary mb-0">Optional CSS applied only to the maintenance page.</p>
            </div>
            <div class="card-body p-4">
                <label class="form-label" for="custom_css">Custom CSS</label>
                <textarea class="form-control font-monospace @error('custom_css') is-invalid @enderror" id="custom_css" name="custom_css" rows="10" spellcheck="false" @disabled(! $canEditMaintenance)>{{ old('custom_css', $maintenanceSetting->custom_css) }}</textarea>
                @error('custom_css')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card sticky-xl-top" style="top: 96px;">
            <div class="card-header">
                <h2 class="h5 mb-1">Current Status</h2>
                <p class="small text-secondary mb-0">Only the first maintenance settings row is used.</p>
            </div>
            <div class="card-body p-4">
                <dl class="row mb-0 small">
                    <dt class="col-5">Configured</dt>
                    <dd class="col-7">{{ $maintenanceSetting->status ? 'Enabled' : 'Disabled' }}</dd>
                    <dt class="col-5">Active now</dt>
                    <dd class="col-7">{{ $maintenanceSetting->isCurrentlyActive() ? 'Yes' : 'No' }}</dd>
                    <dt class="col-5">Mode</dt>
                    <dd class="col-7">{{ $maintenanceSetting->mode === 'full_site' ? 'Full Site' : 'Frontend Only' }}</dd>
                    <dt class="col-5">Updated</dt>
                    <dd class="col-7">{{ $maintenanceSetting->updated_at?->format('M d, Y h:i A') ?: 'Never' }}</dd>
                </dl>
            </div>
            <div class="card-footer bg-white d-grid py-3">
                <button class="btn btn-primary btn-lg" type="submit" @disabled(! $canEditMaintenance)>Save maintenance settings</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('[data-image-input]').forEach((input) => {
            input.addEventListener('change', () => {
                const [file] = input.files;
                const preview = document.getElementById(input.dataset.preview);
                const empty = document.getElementById(input.dataset.empty);
                if (! file || ! preview) return;
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
                empty?.classList.add('d-none');
            });
        });
    </script>
@endpush
