<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $supplierId = $this->route('id');

        return [
            'name' => 'required|string|max:255' . $supplierId,
            'phone' => 'required|string|max:20|unique:suppliers,phone,' . $supplierId,
            'email' => 'required|email' . $supplierId,
            'address' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255'
        ];
    }
}
