<?php

namespace Modules\Account\Http\Requests;

use App\Http\Requests\FormRequest;

class JournalVoucherFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['voucher_no']                = ['required'];
        $this->rules['voucher_date']              = ['required'];
        if(request()->has('journal_account')){
            foreach (request()->journal_account as $key => $value) {
                $this->rules    ['journal_account.'.$key.'.id']             = ['required'];
                $this->messages['journal_account.'.$key.'.id.required']     = 'The account field is required';
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
