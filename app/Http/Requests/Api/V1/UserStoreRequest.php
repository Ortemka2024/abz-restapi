<?php

namespace App\Http\Requests\Api\V1;

use App\Helpers\Api\V1\ApiResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserStoreRequest extends FormRequest
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
            'name' => 'required|string|min:2|max:60',
            'email' => 'required|string|email:rfc,dns|unique:users,email',
            'phone' => ['required', 'string', 'regex:/^\+380\d{9}$/', 'unique:users,phone'],
            'position_id' => 'required|integer|exists:positions,id',
            'photo' => 'required|image|mimes:jpeg,jpg|max:5120|dimensions:min_width=70,min_height=70',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $emailConflict = $errors->has('email') && in_array('The email has already been taken.', $errors->get('email'));
        $phoneConflict = $errors->has('phone') && in_array('The phone has already been taken.', $errors->get('phone'));

        if ($emailConflict || $phoneConflict) {
            $response = ApiResponseHelper::error('User with this phone or email already exist', [], 409);
            throw new HttpResponseException($response);
        }

        $response = ApiResponseHelper::error('Validation failed', $validator->errors(), 422);
        throw new HttpResponseException($response);
    }
}
