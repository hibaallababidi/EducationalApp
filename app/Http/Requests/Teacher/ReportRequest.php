<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
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
            'id' => ['required', 'integer', 'bail', function ($attribute, $value, $fail) {
                if ($this->reported_at_type == 'student' && !\DB::table('students')->where('id', $value)->exists()) {
                    $fail('The selected id is invalid for a student.');
                } elseif ($this->reported_at_type == 'educational' && !\DB::table('educationals')->where('id', $value)->exists()) {
                    $fail('The selected id is invalid for an educational.');
                }
            }],
            'message' => ['required', 'string'],
            'reported_at_type' => [
                'required',
                'string',
                Rule::in(['student', 'educational']),
            ],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
