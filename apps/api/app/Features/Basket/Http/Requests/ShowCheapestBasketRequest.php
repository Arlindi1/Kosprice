<?php

namespace App\Features\Basket\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowCheapestBasketRequest extends FormRequest
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
        ];
    }

    public function cityId(): int
    {
        return (int) $this->validated('city_id');
    }
}
