<?php

namespace Modules\Location\Http\Requests;

use App\Http\Requests\FormRequest;

class LocationFormRequest extends FormRequest
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
        $this->rules['name'] = ['required','string'];
        $this->rules['type'] = ['integer'];
        if(request()->type == 2 || request()->type == 3 || request()->type == 4)
        {
            $this->rules['parent_id'] = ['required'];
            $this->messages['parent_id.requierd'] = 'The field is requied';
        }
        if(request()->type == 3 || request()->type == 4 )
        {
            $this->rules['grand_parent_id'] = ['required'];
            $this->messages['grand_parent_id.requierd'] = 'The field is requied';
        }
        if(request()->type == 4 )
        {
            $this->rules['grand_grand_parent_id'] = ['required'];
            $this->messages['grand_grand_parent_id.requierd'] = 'The field is requied';
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
