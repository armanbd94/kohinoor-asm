<?php

namespace Modules\StockReturn\Http\Requests;

use App\Http\Requests\FormRequest;

class SaleReturnRequest extends FormRequest
{
    protected $rules;
    protected $messages;

    public function rules()
    {
        $this->rules['memo_no']  = ['required'];
        $this->rules['sale_date']   = ['required','date','date_format:Y-m-d'];
        $this->rules['return_date'] = ['required','date','date_format:Y-m-d'];
        $this->rules['customer_name'] = ['required'];

        if(request()->has('products'))
        {
            foreach (request()->products as $key => $value) {
                if($value['return'] == 1)
                {
                    $this->rules['products.'.$key.'.return_qty']     = ['required','numeric','gt:0','lte:'.$value['sold_qty']];
                    $this->rules['products.'.$key.'.deduction_rate'] = ['nullable','numeric','gt:0'];

                    $this->messages['products.'.$key.'.return_qty.required']    = 'The return quantity field is required';
                    $this->messages['products.'.$key.'.return_qty.numeric']     = 'The return quantity value must be numeric';
                    $this->messages['products.'.$key.'.return_qty.gt']          = 'The return quantity value must be greater than 0';
                    $this->messages['products.'.$key.'.deduction_rate.numeric'] = 'The value must be numeric';
                    $this->messages['products.'.$key.'.deduction_rate.gt']      = 'The value must be greater than 0';
                }
            }
        }

        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
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
