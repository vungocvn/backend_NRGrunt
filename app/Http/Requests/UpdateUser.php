<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ];
    }


    public function messages()
    {
        return [
            'required'      => ':attribute không được để trống',
            'exists'        => ':attribute không tồn tại',
            'numeric'       => ':attribute phải là số',
            'max'           => ':attribute tối đa',
            'min'           => ':attribute tối thiểu',
        ];
    }

    public function attributes()
    {
        return [
            'name'    => 'tên người dùng',
            'phone'   => 'số điện thoại',
            'address' => 'địa chỉ',
        ];
    }

}
