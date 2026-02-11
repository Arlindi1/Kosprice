<?php

namespace App\Features\Fuel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListFuelLatestRequest extends FormRequest
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
        ];
    }

    public function cityId(): ?int
    {
        return $this->validated('city_id');
    }
}
