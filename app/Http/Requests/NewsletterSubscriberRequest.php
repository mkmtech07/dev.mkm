<?php

namespace App\Http\Requests;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewsletterSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->cleanText($this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => $this->cleanText($this->input('phone')),
            'notes' => $this->cleanText($this->input('notes')),
            'status_active' => $this->boolean('status_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('newsletter_subscribers', 'email')->ignore($this->route('newsletterSubscriber'))],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['required', Rule::in(NewsletterSubscriber::SOURCES)],
            'status' => ['required', Rule::in(NewsletterSubscriber::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status_active' => ['required', 'boolean'],
        ];
    }

    private function cleanText(mixed $value): ?string
    {
        $value = trim(strip_tags((string) $value));

        return $value === '' ? null : $value;
    }
}
