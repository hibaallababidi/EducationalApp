<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class FollowRequest extends FormRequest
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
            'follower_type' => [
                'required',
                'string',
                Rule::in(['teacher', 'student', 'educational'])
            ],
            //    Rule::in('teacher', 'student', 'educational'),
            'follower_id' => ['integer'],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'status' => ['required', 'boolean'],

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
