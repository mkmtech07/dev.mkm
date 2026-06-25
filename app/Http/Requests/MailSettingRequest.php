<?php

namespace App\Http\Requests;

use App\Models\MailSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MailSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mailer' => $this->input('mailer') ?: 'smtp',
            'host' => $this->blankToNull($this->input('host')),
            'port' => $this->blankToNull($this->input('port')),
            'username' => $this->blankToNull($this->input('username')),
            'password' => $this->blankToNull($this->input('password'), stripTags: false),
            'encryption' => $this->blankToNull($this->input('encryption')) ?: null,
            'from_address' => $this->blankToNull($this->input('from_address')),
            'from_name' => $this->blankToNull($this->input('from_name')),
            'reply_to_address' => $this->blankToNull($this->input('reply_to_address')),
            'reply_to_name' => $this->blankToNull($this->input('reply_to_name')),
            'timeout' => $this->blankToNull($this->input('timeout')),
            'test_recipient' => $this->blankToNull($this->input('test_recipient')),
            'status' => $this->has('status') ? $this->boolean('status') : false,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'mailer' => ['required', Rule::in(MailSetting::MAILERS)],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', Rule::in(MailSetting::ENCRYPTIONS)],
            'from_address' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'reply_to_address' => ['nullable', 'email', 'max:255'],
            'reply_to_name' => ['nullable', 'string', 'max:255'],
            'timeout' => ['nullable', 'integer', 'min:5', 'max:120'],
            'test_recipient' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    private function blankToNull(mixed $value, bool $stripTags = true): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        if ($value === '' || $value === null) {
            return null;
        }

        return $stripTags ? strip_tags((string) $value) : (string) $value;
    }
}
