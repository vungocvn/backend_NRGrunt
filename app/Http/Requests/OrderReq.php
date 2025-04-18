<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderReq extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Xác thực các trường gửi từ frontend lên
     */
    public function rules(): array
    {
        return [
            'cart_ids'       => 'required|array|min:1',
            'cart_ids.*'     => 'integer|exists:carts,id',

            'total_price'    => 'required|numeric',
            'vat'            => 'required|numeric',
            'shipping_fee'   => 'required|numeric',
            'final_total'    => 'required|numeric',

            'receiver_name'    => 'required|string|max:100',
            'receiver_phone'   => 'required|string|max:20',
            'receiver_address' => 'required|string|max:255',
        ];
    }
    public function messages()
    {
        return [
            'cart_ids.required' => 'Bạn chưa chọn giỏ hàng nào!',
            'cart_ids.array'    => 'Dữ liệu giỏ hàng không hợp lệ!',
            'cart_ids.*.exists' => 'Một hoặc nhiều giỏ hàng không tồn tại hoặc đã bị xóa!',

            'total_price.required'  => 'Thiếu tổng tiền đơn hàng!',
            'vat.required'          => 'Thiếu thuế VAT!',
            'shipping_fee.required' => 'Thiếu phí vận chuyển!',
            'final_total.required'  => 'Thiếu tổng thanh toán!',
        ];
    }
    public function attributes()
    {
        return [
            'cart_ids'        => 'Danh sách giỏ hàng',
            'total_price'     => 'Tổng tiền',
            'vat'             => 'VAT',
            'shipping_fee'    => 'Phí vận chuyển',
            'final_total'     => 'Thành tiền',
            'receiver_name'   => 'Tên người nhận',
            'receiver_phone'  => 'Số điện thoại người nhận',
            'receiver_address'=> 'Địa chỉ người nhận',
        ];
    }

}
