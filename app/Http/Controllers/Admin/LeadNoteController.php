<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadNoteRequest;
use App\Models\Lead;
use App\Models\LeadNote;
use Illuminate\Http\RedirectResponse;

class LeadNoteController extends Controller
{
    public function store(LeadNoteRequest $request, Lead $lead): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()?->id;
        $lead->notes()->create($data);

        if ($data['next_follow_up_date'] ?? null) {
            $lead->update(['follow_up_date' => $data['next_follow_up_date']]);
        }

        return back()->with('success', 'Lead note added successfully.');
    }

    public function destroy(Lead $lead, LeadNote $leadNote): RedirectResponse
    {
        abort_unless($leadNote->lead_id === $lead->id, 404);
        $leadNote->delete();

        return back()->with('success', 'Lead note deleted successfully.');
    }
}
