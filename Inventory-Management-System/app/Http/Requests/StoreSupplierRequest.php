<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:suppliers,phone',
            'email' => 'required|email|unique:suppliers,email',
            'address' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255'
        ];
    }
}

