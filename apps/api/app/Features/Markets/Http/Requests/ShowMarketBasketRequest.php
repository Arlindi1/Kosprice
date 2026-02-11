<?php

namespace App\Features\Markets\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowMarketBasketRequest extends FormRequest
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
            'recorded_at' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function recordedAt(): ?string
    {
        return $this->validated('recorded_at');
    }
}
