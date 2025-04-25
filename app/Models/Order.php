<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'order_code',
        'total_price',
        'vat',
        'shipping_fee',
        'final_total',
        'cart_ids',
        'is_paid',
        'is_canceled',
        'is_confirmed',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
    ];

    public function orderDetails()
    {
        return $this->hasMany(DetailOrder::class, 'order_id');
    }
    const STATUS_TEXT = [
        0 => 'Xác nhận đơn',
        1 => 'Đã hoàn thành',
        2 => 'Đã hủy',
    ];

    public function getStatusTextAttribute()
    {
        return self::STATUS_TEXT[$this->status] ?? 'Không xác định';
    }
    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}


}
