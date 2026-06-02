<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slots' => 'required|array',
            'slots.*.uuid' => 'nullable|uuid',
            'slots.*.plan_uuid' => 'required|uuid',
            'slots.*.exercise_name' => 'required|string',
            'slots.*.exercise_order' => 'nullable|integer',
            'slots.*.day' => 'required|string',
            'slots.*.metrics_type' => 'nullable|string',
            'slots.*.metrics_data' => 'nullable|array',
            'slots.*.meta_data' => 'required|array',
            'slots.*.meta_data.target_muscles' => 'required_unless:slots.*.metrics_type,rest|array|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'slots.required' => 'The slots array is required',
            'slots.array' => 'The slots must be an array',
            'slots.*.uuid.uuid' => 'The slot UUID must be a valid UUID',
            'slots.*.plan_uuid.required' => 'The plan UUID is required for each slot',
            'slots.*.plan_uuid.uuid' => 'The plan UUID must be a valid UUID',
            'slots.*.exercise_name.required' => 'The exercise name is required',
            'slots.*.exercise_name.string' => 'The exercise name must be a string',
            'slots.*.exercise_order.integer' => 'The exercise order must be an integer',
            'slots.*.day.required' => 'The day is required for each slot',
            'slots.*.day.string' => 'The day must be a string',
            'slots.*.meta_data.target_muscles.required_unless' => 'At least one targeted muscle is required for each exercise.',
            'slots.*.meta_data.target_muscles.min' => 'At least one targeted muscle is required for each exercise.',
        ];
    }
}
