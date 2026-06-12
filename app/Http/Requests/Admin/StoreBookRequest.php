<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')],
            'accent_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'file' => ['required', 'file', 'mimes:pdf,epub', 'max:51200'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
