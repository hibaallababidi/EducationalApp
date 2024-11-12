<?php

namespace App\Http\Requests\Teacher\Profile;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TeacherEditProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['string'],
            'last_name' => ['string'],
            'details' => ['string'],
            'specializations' => ['array'],
            'specializations.*' => ['int', 'exists:specializations,id'],
            'location_id' => ['integer', 'exists:locations,id'],
            'photo' => ['image'],
            'phone_number' => ['string', 'min:10'],
            'cv' => ['file'],
            'social_links' => ['array'],
            'social_links.*' => ['array'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
