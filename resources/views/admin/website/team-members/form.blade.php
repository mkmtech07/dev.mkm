<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Member profile</h2>
                <p class="text-secondary small mb-0">Add the team member's public profile information.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                        <input
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $teamMember->name) }}"
                            maxlength="255"
                            required
                        >
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="designation">Designation</label>
                        <input
                            class="form-control @error('designation') is-invalid @enderror"
                            id="designation"
                            name="designation"
                            type="text"
                            value="{{ old('designation', $teamMember->designation) }}"
                            maxlength="255"
                        >
                        @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div>
                    <label class="form-label" for="bio">Bio</label>
                    <textarea
                        class="form-control @error('bio') is-invalid @enderror"
                        id="bio"
                        name="bio"
                        rows="7"
                        maxlength="5000"
                    >{{ old('bio', $teamMember->bio) }}</textarea>
                    @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Contact details</h2>
                <p class="text-secondary small mb-0">Optional public email and phone information.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input
                            class="form-control @error('email') is-invalid @enderror"
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $teamMember->email) }}"
                            maxlength="255"
                        >
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="phone">Phone</label>
                        <input
                            class="form-control @error('phone') is-invalid @enderror"
                            id="phone"
                            name="phone"
                            type="text"
                            value="{{ old('phone', $teamMember->phone) }}"
                            maxlength="50"
                        >
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">Social links</h2>
                <p class="text-secondary small mb-0">Use complete links to public social profiles.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach ([
                        'facebook_url' => ['Facebook URL', 'https://facebook.com/profile'],
                        'linkedin_url' => ['LinkedIn URL', 'https://linkedin.com/in/profile'],
                        'twitter_url' => ['X / Twitter URL', 'https://x.com/profile'],
                    ] as $field => [$label, $placeholder])
                        <div class="col-12">
                            <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                            <input
                                class="form-control @error($field) is-invalid @enderror"
                                id="{{ $field }}"
                                name="{{ $field }}"
                                type="url"
                                value="{{ old($field, $teamMember->{$field}) }}"
                                maxlength="2048"
                                placeholder="{{ $placeholder }}"
                            >
                            @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Profile image</h2>
                <p class="text-secondary small mb-0">Upload an optional square portrait.</p>
            </div>
            <div class="card-body p-4">
                <img
                    id="team-image-preview"
                    class="rounded-circle object-fit-cover border mb-3 {{ $teamMember->image ? '' : 'd-none' }}"
                    @if ($teamMember->image) src="{{ asset($teamMember->image) }}" @endif
                    alt="Team member image preview"
                    width="160"
                    height="160"
                >
                <input
                    class="form-control @error('image') is-invalid @enderror"
                    id="image"
                    name="image"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp"
                >
                <div class="form-text">JPG, PNG, or WebP. Maximum 4 MB.</div>
                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Display settings</h2>
                <p class="text-secondary small mb-0">Control visibility and ordering.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label" for="sort_order">Sort order <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('sort_order') is-invalid @enderror"
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        value="{{ old('sort_order', $teamMember->sort_order ?? 0) }}"
                        min="0"
                        required
                    >
                    <div class="form-text">Lower numbers appear first.</div>
                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input
                        class="form-check-input"
                        id="status"
                        name="status"
                        type="checkbox"
                        value="1"
                        @checked(old('status', $teamMember->status ?? true))
                    >
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ route('admin.team-members.index') }}">Cancel</a>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('image')?.addEventListener('change', (event) => {
            const [file] = event.target.files;
            const preview = document.getElementById('team-image-preview');

            if (! file || ! preview) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });
    </script>
@endpush
