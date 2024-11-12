<?php

namespace App\Http\Requests\Teacher\PrivateLesson;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EditLessonRequest extends FormRequest
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
            'lesson_id' => ['required', 'int', 'exists:private_lessons,id'],
            'lesson_date' => ['after_or_equal:' . date('Y-m-d H:i:s')],
            'price' => ['regex:/^\d+(\.\d{1,2})?$/'],
            'meet_link' => ['string']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}