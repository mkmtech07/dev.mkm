<?php

namespace App\Http\Requests;

use App\Models\LeadNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string'],
            'note_type' => ['required', Rule::in(LeadNote::TYPES)],
            'next_follow_up_date' => ['nullable', 'date'],
        ];
    }
}
