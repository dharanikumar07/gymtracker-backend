<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $this->user()->uuid . ',uuid'],
            'fitness_data' => ['required', 'array'],
            'fitness_data.age' => ['required', 'numeric', 'min:1', 'max:120'],
            'fitness_data.gender' => ['required', 'string', 'in:male,female,other'],
            'fitness_data.height' => ['required', 'numeric', 'min:50', 'max:300'],
            'fitness_data.weight' => ['required', 'numeric', 'min:20', 'max:500'],
            'fitness_data.fitness_goal' => ['required', 'string'],
            'fitness_data.physical_activity_type' => ['required', 'string'],
        ];
    }
}
