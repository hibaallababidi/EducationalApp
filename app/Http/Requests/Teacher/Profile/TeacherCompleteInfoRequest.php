<?php

namespace App\Http\Requests\Teacher\Profile;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TeacherCompleteInfoRequest extends FormRequest
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
            'details' => ['required', 'string'],
            'specializations' => ['required', 'array'],
            'specializations.*' => ['required', 'int', 'exists:specializations,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'photo' => ['image'],
            'phone_number' => ['required', 'string', 'min:10'],
            'cv' => ['required'],
            'social_links' => ['array'],
            'social_links.*' => ['array'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
