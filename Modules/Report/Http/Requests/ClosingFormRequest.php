<?php

namespace Modules\Report\Http\Requests;

use App\Http\Requests\FormRequest;

class ClosingFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'last_day_closing' => 'nullable|numeric|gte:0',
            'cash_in' => 'nullable|numeric|gte:0',
            'cash_out' => 'nullable|numeric|gte:0',
            'balance' => 'nullable|numeric|gte:0',
            'transfer' => 'nullable|numeric|gte:0',
            'cash_in_hand' => 'nullable|numeric|gte:0',
        ];
    }

    public function messages()
    {
        return [
            'cash_in_hand.nullable' => 'Closing balance field value can be nullable',
            'cash_in_hand.numeric' => 'Closing balance field value must be numeric',
            'cash_in_hand.gte' => 'Closing balance field value must be greater than or equal to 0',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
