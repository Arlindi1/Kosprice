<?php

namespace App\Features\Basket\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListBasketTrendRequest extends FormRequest
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
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ];
    }

    public function cityId(): int
    {
        return (int) $this->validated('city_id');
    }

    public function days(): int
    {
        return (int) $this->validated('days', 30);
    }
}
