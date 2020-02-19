<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResponseCreateRequest extends FormRequest
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
            'forum_id' => 'required|integer',
            'content' => 'required',
            'sentiment' => 'null',
            'user_id' => 'null',
            'image_response_id' => 'null',
            'image_forum_id' => 'null'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response['errors'] = $validator->errors()->toArray();
        throw new HttpResponseException(
            response()->json($response, 422)
        );
    }
}
