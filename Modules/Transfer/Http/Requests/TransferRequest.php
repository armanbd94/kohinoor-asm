<?php

namespace Modules\Transfer\Http\Requests;

use App\Http\Requests\FormRequest;

class TransferRequest extends FormRequest
{

    public function rules()
    {
        $rules = [];
        $rules['chalan_no']           = ['required','unique:transfers,chalan_no'];
        $rules['transfer_date']       = ['required','date','date_format:Y-m-d'];
        $rules['warehouse_id']        = ['required'];
        $rules['transfer_status']     = ['required'];
        $rules['shipping_cost']       = ['nullable','numeric','gt:0'];
        $rules['labor_cost']          = ['nullable','numeric','gt:0'];
        $rules['received_by']         = ['required'];
        $rules['carried_by']          = ['required'];
        return $rules;
    }

    public function authorize()
    {
        return true;
    }
}
