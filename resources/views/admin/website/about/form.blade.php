<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">About content</h2>
                <p class="text-secondary small mb-0">Introduce the business and what makes it different.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('title') is-invalid @enderror"
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title', $aboutSection->title) }}"
                        maxlength="255"
                        required
                    >
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="subtitle">Subtitle</label>
                    <input
                        class="form-control @error('subtitle') is-invalid @enderror"
                        id="subtitle"
                        name="subtitle"
                        type="text"
                        value="{{ old('subtitle', $aboutSection->subtitle) }}"
                        maxlength="500"
                    >
                    @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="description">Description <span class="text-danger">*</span></label>
                    <textarea
                        class="form-control @error('description') is-invalid @enderror"
                        id="description"
                        name="description"
                        rows="7"
                        maxlength="10000"
                        required
                    >{{ old('description', $aboutSection->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">Mission and vision</h2>
                <p class="text-secondary small mb-0">Share the purpose and direction of the business.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="mission">Mission</label>
                        <textarea
                            class="form-control @error('mission') is-invalid @enderror"
                            id="mission"
                            name="mission"
                            rows="6"
                            maxlength="5000"
                        >{{ old('mission', $aboutSection->mission) }}</textarea>
                        @error('mission') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="vision">Vision</label>
                        <textarea
                            class="form-control @error('vision') is-invalid @enderror"
                            id="vision"
                            name="vision"
                            rows="6"
                            maxlength="5000"
                        >{{ old('vision', $aboutSection->vision) }}</textarea>
                        @error('vision') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Image and visibility</h2>
                <p class="text-secondary small mb-0">Choose the public image and active section.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label" for="image">About image</label>
                    @if ($aboutSection->image)
                        <div class="mb-3">
                            <img
                                class="img-fluid rounded border"
                                src="{{ asset($aboutSection->image) }}"
                                alt="Current About image"
                            >
                        </div>
                    @endif
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

                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input
                        class="form-check-input"
                        id="status"
                        name="status"
                        type="checkbox"
                        value="1"
                        @checked(old('status', $aboutSection->status ?? true))
                    >
                    <label class="form-check-label" for="status">Active</label>
                </div>
                <div class="form-text">Activating this record automatically deactivates the current one.</div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Statistics</h2>
                <p class="text-secondary small mb-0">Leave any statistic blank to hide it.</p>
            </div>
            <div class="card-body p-4">
                @foreach ([
                    'years_of_experience' => 'Years of experience',
                    'projects_completed' => 'Projects completed',
                    'clients_served' => 'Clients served',
                    'team_members' => 'Team members',
                ] as $field => $label)
                    <div class="mb-3">
                        <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                        <input
                            class="form-control @error($field) is-invalid @enderror"
                            id="{{ $field }}"
                            name="{{ $field }}"
                            type="number"
                            value="{{ old($field, $aboutSection->{$field}) }}"
                            min="0"
                        >
                        @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ route('admin.about.index') }}">Cancel</a>
        </div>
    </div>
</div>
