<div class="card content-card">
    <div class="card-header">
        <h2 class="h5 mb-1">Menu details</h2>
        <p class="small text-secondary mb-0">Name the menu and choose where it can be displayed.</p>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-7">
                <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $menu->name) }}" maxlength="255" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-5">
                <label class="form-label" for="location">Location <span class="text-danger">*</span></label>
                <select class="form-select @error('location') is-invalid @enderror" id="location" name="location" required>
                    @foreach (['header' => 'Header', 'footer' => 'Footer', 'sidebar' => 'Sidebar'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('location', $menu->location) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" id="status" name="status" type="checkbox" value="1" @checked(old('status', $menu->status ?? true))>
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
        <a class="btn btn-light" href="{{ route('admin.menus.index') }}">Cancel</a>
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    </div>
</div>
