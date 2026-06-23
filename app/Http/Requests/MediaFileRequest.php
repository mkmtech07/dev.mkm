<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class MediaFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->cleanText($this->input('title')),
            'alt_text' => $this->cleanText($this->input('alt_text')),
            'caption' => $this->cleanText($this->input('caption')),
            'status' => $this->has('status') ? $this->boolean('status') : false,
        ]);
    }

    /** @return array<string, ValidationRule|Closure|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'file' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,svg,gif,pdf,doc,docx,xls,xlsx',
                'max:5120',
                $this->safeSvg(...),
            ],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    private function cleanText(mixed $value): mixed
    {
        return is_string($value) ? trim(strip_tags($value)) : $value;
    }

    private function safeSvg(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || strtolower($value->getClientOriginalExtension()) !== 'svg') {
            return;
        }

        $contents = file_get_contents($value->getRealPath());

        if ($contents === false || preg_match('~<script|<foreignObject|\bon\w+\s*=|javascript:|<!ENTITY|<!DOCTYPE~i', $contents)) {
            $fail('The SVG file contains unsafe content.');
        }
    }
}
