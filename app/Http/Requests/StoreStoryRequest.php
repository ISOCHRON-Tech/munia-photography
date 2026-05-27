<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', 'unique:stories,slug'],
            'content'          => ['required', 'string'],
            'banner'           => [
                'nullable',
                File::types(['image/jpeg', 'image/png', 'image/webp'])
                    ->max(10 * 1024),
            ],
            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'status'           => ['required', 'in:draft,published'],
            'published_at'     => ['nullable', 'date'],
        ];
    }
}
