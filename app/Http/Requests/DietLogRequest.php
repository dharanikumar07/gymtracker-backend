<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DietLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'date_format:Y-m-d'],
            'day' => ['required', 'string', 'in:mon,tue,wed,thu,fri,sat,sun'],
            'plan_uuid' => ['required', 'uuid'],
            'logs' => ['required', 'array', 'min:1'],
            'logs.*.meal_plan_uuid' => ['nullable', 'uuid'],
            'logs.*.meal_name' => ['required', 'string', 'max:255'],
            'logs.*.time_period' => ['required', 'string', 'in:breakfast,lunch,dinner,snack'],
            'logs.*.calories' => ['nullable', 'numeric'],
            'logs.*.protein' => ['nullable', 'numeric'],
            'logs.*.carbs' => ['nullable', 'numeric'],
            'logs.*.fats' => ['nullable', 'numeric'],
            'logs.*.sync_with_setup' => ['nullable', 'boolean'],
            'logs.*.food_data' => ['required', 'array'],
            'logs.*.type' => ['nullable', 'string'],
            'logs.*.status' => ['nullable', 'string'],
            'logs.*.reason' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'activity_date.required' => 'Activity date is required',
            'activity_date.date_format' => 'Activity date must be in YYYY-MM-DD format',
            'day.required' => 'Day is required',
            'day.in' => 'Day must be a valid day (mon, tue, wed, thu, fri, sat, sun)',
            'logs.required' => 'At least one log entry is required',
            'logs.*.meal_name.required' => 'Meal name is required',
            'logs.*.time_period.required' => 'Time period is required',
            'logs.*.food_data.required' => 'Food data is required for each log',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
