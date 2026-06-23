<form method="POST" action="{{ route('admin.leads.notes.store', $lead) }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="note_type">Activity type</label><select class="form-select @error('note_type') is-invalid @enderror" id="note_type" name="note_type">@foreach (\App\Models\LeadNote::TYPES as $item)<option value="{{ $item }}" @selected(old('note_type', 'general') === $item)>{{ \App\Models\Lead::label($item) }}</option>@endforeach</select>@error('note_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-8"><label class="form-label" for="next_follow_up_date">Next follow-up</label><input class="form-control @error('next_follow_up_date') is-invalid @enderror" id="next_follow_up_date" name="next_follow_up_date" type="datetime-local" value="{{ old('next_follow_up_date') }}">@error('next_follow_up_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><label class="form-label" for="note">Note <span class="text-danger">*</span></label><textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="4" required placeholder="Record the call, email, meeting, or next action...">{{ old('note') }}</textarea>@error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 text-end"><button class="btn btn-primary" type="submit">Add activity</button></div>
    </div>
</form>
