<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|uuid',
            'name' => 'required|string',
            'type' => 'required|string',
            'meta_data' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The plan name is required',
            'type.required' => 'The plan type is required',
            'uuid.uuid' => 'The plan UUID must be a valid UUID',
            'start_date.date' => 'The start date must be a valid date',
            'end_date.date' => 'The end date must be a valid date',
        ];
    }
}
