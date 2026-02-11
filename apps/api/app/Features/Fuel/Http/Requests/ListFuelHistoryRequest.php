<?php

namespace App\Features\Fuel\Http\Requests;

use App\Features\Fuel\Enums\FuelType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListFuelHistoryRequest extends FormRequest
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
            'days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ];
    }

    public function cityId(): ?int
    {
        return $this->validated('city_id');
    }

    public function type(): ?string
    {
        return $this->validated('type');
    }

    public function days(): int
    {
        return (int) $this->validated('days', 30);
    }
}
