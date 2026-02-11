<?php

namespace App\Features\Basket\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowBasketTotalRequest extends FormRequest
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
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'market_id' => ['required', 'integer', 'exists:markets,id'],
        ];
    }

    public function cityId(): ?int
    {
        return $this->validated('city_id');
    }

    public function marketId(): int
    {
        return (int) $this->validated('market_id');
    }
}
