<?php

namespace Crater\Http\Requests;

use Crater\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Crater\Rules\UniqueNumber;

class ExpenseRequest extends FormRequest
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
            'expense_date' => [
                'required'
            ],
            'expense_due' => [
                'required'
            ],
            'expense_category_id' => [
                'required'
            ],
            'doc_num' => [
                'required',
                new UniqueNumber(Expense::class)
            ],
            'user_id' => [
                'required'
            ],
            'notes' => [
                'nullable'
            ],
            'discount' => [
                'required'
            ],
            'discount_val' => [
                'required'
            ],
            'sub_total' => [
                'required'
            ],
            'total' => [
                'required'
            ],
            'tax' => [
                'required'
            ],
            'items' => [
                'required',
            ],
            'items.*' => [
                'required',
                'max:255'
            ],
            'items.*.description' => [
                'max:255'
            ],
            'items.*.name' => [
                'required'
            ],
            'items.*.quantity' => [
                'required'
            ],
            'items.*.price' => [
                'required'
            ]
        ];

        if ($this->_method === 'PUT') {
            $rules['doc_num'] = [
                new UniqueNumber(Expense::class, $this->route('expense')->id)
            ];
        }

        return $rules;
    }
}
