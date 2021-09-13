<?php

namespace Modules\Product\Http\Requests;

use App\Http\Requests\FormRequest;

class BarcodeFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_code'     => 'required',
            'barcode_qty' => 'required|integer|gt:0',
            'row_qty'     => 'required|integer|gt:0',
        ];
    }

    public function messages()
    {
        return [
            'product_code.required' => 'Please select product',
            'barcode_qty.required' => 'The no of barcode field is required',
            'barcode_qty.integer' => 'The no of barcode value must be integer',
            'barcode_qty.gt:0' => 'The no of barcode value must be greater than 0',
            'row_qty.required' => 'The each row field is required',
            'row_qty.integer' => 'The each row value must be integer',
            'row_qty.gt:0' => 'The each row value must be greater than 0',
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
