<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name'          => 'required|unique:products',
            'name_ar'       => 'required|unique:products',
            'price'         => 'required|numeric',
            'quantity'      => 'required|numeric',
            'details'       => 'required',
            'details_ar'    => 'required',
            'brand_id'      => 'required',
            'category_id'   => 'required',
        ];
    }

    public function messages()
    {
        return [
            'brand_id.required'     => 'The Brand field is required.',
            'category_id.required'  => 'The Category field is required.',
        ];
    }
}
