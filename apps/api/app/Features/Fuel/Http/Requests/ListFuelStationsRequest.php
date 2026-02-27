<?php

namespace App\Features\Fuel\Http\Requests;

use App\Features\Fuel\Enums\FuelType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListFuelStationsRequest extends FormRequest
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
            'type' => ['nullable', Rule::in(FuelType::values())],
            'fuel_type' => ['nullable', Rule::in(FuelType::values())],
        ];
    }

    public function cityId(): ?int
    {
        return $this->validated('city_id');
    }

    public function fuelType(): ?string
    {
        return $this->validated('type') ?? $this->validated('fuel_type');
    }
}

