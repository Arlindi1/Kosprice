<?php

namespace App\Features\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:60'],
        ];
    }

    public function category(): ?string
    {
        return $this->validated('category');
    }
}
