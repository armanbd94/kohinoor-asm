<?php

namespace Modules\Product\Http\Requests;

use App\Http\Requests\FormRequest;

class AdjustmentFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['adjustment_no'] = ['required','unique:adjustments,adjustment_no'];
        $this->rules['warehouse_id']  = ['required'];
        $this->rules['note']          = ['required'];
        if(request()->update_id)
        {
            $this->rules['adjustment_no'] = 'unique:adjustments,adjustment_no,'.request()->update_id;
        }

        if(request()->has('products'))
        {
            foreach (request()->products as $key => $value) {
                $this->rules   ['products.'.$key.'.batch_no'] = ['required'];
                $this->rules   ['products.'.$key.'.base_unit_qty']          = ['required','numeric','gt:0'];
                $this->messages['products.'.$key.'.base_unit_qty.required'] = 'This field is required';
                $this->messages['products.'.$key.'.base_unit_qty.numeric']  = 'The value must be numeric';
                $this->messages['products.'.$key.'.base_unit_qty.gt']       = 'The value must be greater than 0';
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
