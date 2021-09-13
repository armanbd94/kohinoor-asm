<?php

namespace Modules\Product\Http\Requests;

use App\Http\Requests\FormRequest;

class ProductFormRequest extends FormRequest
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
        $this->rules['name']              = ['required','string','unique:products,name'];
        $this->rules['code']              = ['required','string','unique:products,code'];
        $this->rules['product_type']      = ['required'];
        $this->rules['category_id']       = ['required'];
        $this->rules['barcode_symbology'] = ['required'];
        $this->rules['tax_id']            = ['nullable','numeric'];
        $this->rules['tax_method']        = ['required','numeric'];
        $this->rules['description']       = ['nullable','string'];
        $this->rules['image']             = ['nullable','image','mimes:png,jpg,jpeg,svg,webp'];
        
        if(request()->update_id){
            $this->rules['name'][2] = 'unique:products,name,'.request()->update_id;
            $this->rules['code'][2] = ['required','string','unique:products,code,'.request()->update_id];
            
        }
        $this->rules['base_unit_id']    = ['required'];
        $this->rules['unit_id']         = ['required'];
        $this->rules['alert_quantity']  = ['nullable','numeric','gte:0'];
        $this->rules['base_unit_mrp']   = ['required','numeric','gt:0'];
        $this->rules['base_unit_price'] = ['required','numeric','gt:0'];
        $this->rules['unit_mrp']        = ['required','numeric','gt:0'];
        $this->rules['unit_price']      = ['required','numeric','gt:0'];
        
        $this->messages['unit_id.required']      = 'The unit field is required';
        $this->messages['base_unit_id.required'] = 'The base unit field is required';
       
        $collection = collect(request());
        if($collection->has('materials')){
            foreach (request()->materials as $key => $value) {
                $this->rules   ['materials.'.$key.'.id']           = ['required','integer'];

                $this->messages['materials.'.$key.'.id.required']  = 'The material name field is required';
                $this->messages['materials.'.$key.'.id.integer']   = 'The material name field value must be integer';
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
