<?php
namespace Crater\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompaniesRequest extends FormRequest
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
        $rules = [
            'companyName' => [
                'required'
            ],
            'cif' => [
                'required'
            ],
            'name' => [
                'required'
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')
            ],
            'password' => [
                'required',
                'min:8'
            ]
        ];
        if ($this->getMethod() == 'PUT') {
            $rules['email'] = [
                'required',
                'email',
                Rule::unique('users')->ignore($this->route('company'))
            ];
            $rules['password'] = [
                'nullable',
                'min:8'
            ];
        }

        return $rules;
    }
}