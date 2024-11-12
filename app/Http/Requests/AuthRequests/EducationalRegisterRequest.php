<?php

namespace App\Http\Requests\AuthRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class EducationalRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:4'],
            'email' => ['required', 'email', 'unique:educationals', 'unique:teachers', 'unique:students'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'type' => Rule::in('public_school', 'center', 'institute', 'private_school'),
            'phone_number' => ['required', 'string', 'min:10'],
//            'device_token' => ['required', 'string']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
