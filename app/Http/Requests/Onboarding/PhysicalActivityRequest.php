<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class PhysicalActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_uuid' => 'nullable|string',
            'physical_activity_type' => 'required|string',
            'weekly_split' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'physical_activity_type.required' => 'Physical activity type is required',
            'weekly_split.required' => 'Weekly split data is required',
            'weekly_split.array' => 'Weekly split must be an array',
        ];
    }
}
