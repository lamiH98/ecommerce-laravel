<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'question'      => 'required|min:3',
            'question_ar'   => 'required|min:3',
            'answer'        => 'required|min:3',
            'answer_ar'     => 'required|min:3',
        ];
    }
}
