<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class WorkoutLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activity_date' => ['required', 'date', 'date_format:Y-m-d'],
            'day' => ['required', 'string', 'size:3', 'in:mon,tue,wed,thu,fri,sat,sun'],
            'plan_uuid' => ['required', 'uuid'],
            'logs' => ['required', 'array', 'min:1'],
            'logs.*.slot_uuid' => ['nullable', 'uuid'],
            'logs.*.exercise_name' => ['required_without:logs.*.slot_uuid', 'nullable', 'string', 'max:255'],
            'logs.*.metrics_type' => ['nullable', 'string'],
            'logs.*.metrics_data' => ['required', 'array'],
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
            'logs.*.metrics_data.required' => 'Metrics data is required for each log',
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
