<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ProfileInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'age' => 'required|integer|min:10|max:120',
            'gender' => 'required|string|in:male,female,other',
            'height' => 'required|numeric|min:50|max:300',
            'weight' => 'required|numeric|min:20|max:500',
            'fitness_goal' => 'required|string|in:muscle_gain,weight_loss,maintenance',
            'physical_activity_type' => 'required|string|in:strength_training,cardio,flexibility,balance,calisthenics',
        ];
    }

    public function messages(): array
    {
        return [
            'age.required' => 'Age is required',
            'age.integer' => 'Age must be a valid number',
            'age.min' => 'Age must be at least 10 years',
            'age.max' => 'Age must not exceed 120 years',
            'gender.required' => 'Gender is required',
            'gender.in' => 'Gender must be male, female, or other',
            'height.required' => 'Height is required',
            'height.numeric' => 'Height must be a valid number',
            'height.min' => 'Height must be at least 50 cm',
            'height.max' => 'Height must not exceed 300 cm',
            'weight.required' => 'Weight is required',
            'weight.numeric' => 'Weight must be a valid number',
            'weight.min' => 'Weight must be at least 20 kg',
            'weight.max' => 'Weight must not exceed 500 kg',
            'fitness_goal.required' => 'Fitness goal is required',
            'fitness_goal.in' => 'Fitness goal must be muscle_gain, weight_loss, or maintenance',
            'physical_activity_type.required' => 'Training type is required',
            'physical_activity_type.in' => 'Training type must be strength_training, cardio, flexibility, balance, or calisthenics',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            Response::json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray()
            ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
