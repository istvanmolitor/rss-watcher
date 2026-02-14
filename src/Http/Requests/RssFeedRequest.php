<?php

namespace Molitor\RssWatcher\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RssFeedRequest",
    required: ["name", "url"],
    properties: [
        new OA\Property(property: "name", type: "string", example: "Tech News"),
        new OA\Property(property: "url", type: "string", format: "url", example: "https://example.com/feed.xml"),
        new OA\Property(property: "enabled", type: "boolean", example: true),
    ]
)]
class RssFeedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add ACL check if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:512'],
            'enabled' => ['boolean'],
        ];

        return $rules;
    }
}

