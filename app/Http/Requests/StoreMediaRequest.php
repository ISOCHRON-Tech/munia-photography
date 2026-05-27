<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * Validates a media upload with strict MIME-type and size constraints.
 * UUID-rename happens in the controller / job after validation passes.
 */
class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Admin-only — protect via route middleware; always return true here
        return true;
    }

    public function rules(): array
    {
        return [
            'images'                => ['required', 'array', 'min:1', 'max:20'],
            'images.*'              => [
                'required',
                File::types(['image/jpeg', 'image/png', 'image/webp'])
                    ->max(25 * 1024),  // 25 MB per image
            ],
            'title'                 => ['nullable', 'string', 'max:200'],
            'description'           => ['nullable', 'string', 'max:2000'],
            'category_id'           => ['nullable', 'integer', 'exists:categories,id'],
            'tags'                  => ['nullable', 'array'],
            'tags.*'                => ['string', 'max:50'],
            'is_featured'           => ['boolean'],
        ];
    }

    /** Provide human-friendly attribute names for validation messages. */
    public function attributes(): array
    {
        return [
            'images.*' => 'image file',
        ];
    }
}
