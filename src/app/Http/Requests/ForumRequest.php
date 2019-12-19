<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ForumRequest extends FormRequest
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
            'title' => 'required',
            'image' => 'file|mimes:jpg,jpeg,png,gif'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response['errors']  = $validator->errors()->toArray();
        throw new HttpResponseException(
            response()->json($response, 422)
        );
    }
}
