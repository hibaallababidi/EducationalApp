<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DisplayTeacherInfoRequest extends FormRequest
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
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'type_id' => [
                'required',
                'integer',
                'bail',
                function ($attribute, $value, $fail) {
                    $type = $this->input('type'); // Access the type value from the request
                    if ($type == 'student' && !DB::table('students')->where('id', $value)->exists()) {
                        $fail('The selected id is invalid for a student.');
                    } elseif ($type == 'educational' && !DB::table('educationals')->where('id', $value)->exists()) {
                        $fail('The selected id is invalid for an educational.');
                    } elseif ($type == 'teacher' && !DB::table('teachers')->where('id', $value)->exists()) {
                        $fail('The selected id is invalid for a teacher.');
                    }
                }
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['teacher', 'student', 'educational']),
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
