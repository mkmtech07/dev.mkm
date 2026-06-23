<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['status' => $this->boolean('status')]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $menuId = $this->route('menu')?->getKey();
        $menuItem = $this->route('menuItem');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('menu_items', 'id')
                    ->where(fn ($query) => $query
                        ->where('menu_id', $menuId)
                        ->whereNull('deleted_at')),
                Rule::notIn(array_filter([$menuItem?->getKey()])),
            ],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(MenuItem::TYPES)],
            'page_id' => [
                'nullable',
                'required_if:type,page',
                'integer',
                Rule::exists('pages', 'id')->whereNull('deleted_at'),
            ],
            'blog_id' => [
                'nullable',
                'required_if:type,blog',
                'integer',
                Rule::exists('blogs', 'id')->whereNull('deleted_at'),
            ],
            'blog_category_id' => [
                'nullable',
                'required_if:type,blog_category',
                'integer',
                Rule::exists('blog_categories', 'id')->whereNull('deleted_at'),
            ],
            'url' => [
                'nullable',
                'required_if:type,custom_url',
                'string',
                'max:2048',
                'regex:~^(?:/(?!/)|https?://|mailto:|tel:|#)~i',
            ],
            'icon' => ['nullable', 'string', 'max:255'],
            'target' => ['required', Rule::in(MenuItem::TARGETS)],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $menuItem = $this->route('menuItem');
            $parentId = $this->integer('parent_id');

            if (! $menuItem || ! $parentId || $validator->errors()->has('parent_id')) {
                return;
            }

            $parent = MenuItem::query()
                ->where('menu_id', $this->route('menu')->getKey())
                ->find($parentId);
            $visited = [];

            while ($parent) {
                if (isset($visited[$parent->id])) {
                    $validator->errors()->add('parent_id', 'The selected parent has an invalid nesting cycle.');

                    return;
                }

                $visited[$parent->id] = true;

                if ($parent->is($menuItem)) {
                    $validator->errors()->add('parent_id', 'A menu item cannot be nested below one of its children.');

                    return;
                }

                $parent = $parent->parent;
            }
        });
    }

    public function messages(): array
    {
        return [
            'url.regex' => 'Enter an internal path, secure web URL, email link, phone link, or anchor.',
        ];
    }
}
