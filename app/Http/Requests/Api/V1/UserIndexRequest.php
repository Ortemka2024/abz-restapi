<?php

namespace App\Http\Requests\Api\V1;

use App\Helpers\Api\V1\ApiResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserIndexRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'count' => 'nullable|integer|min:1',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'count.integer' => 'The count must be an integer.',
            'count.min' => 'The count must be at least 1.',
            'page.integer' => 'The page must be an integer.',
            'page.min' => 'The page must be at least 1.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = ApiResponseHelper::error(
            'Validation failed',
            $validator->errors(),
            422
        );

        throw new HttpResponseException($response);
    }
}
