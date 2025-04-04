<?php

namespace App\Repository\impl;

use App\Exceptions\APIException;
use App\Models\Order;
use App\Repository\BaseRepository;
use App\Repository\extend\IOrderRepo;
use Illuminate\Support\Str;

class OrderRepo extends BaseRepository implements IOrderRepo
{
    private function findOrder($id = null, $userId = null, $filters = [])
    {
        $query = Order::query()
            ->when($userId !== null, fn($q) => $q->where('user_id', $userId))
            ->when(isset($filters['is_paid']), fn($q) => $q->where('is_paid', $filters['is_paid']));

        return $id
            ? $query->where('id', $id)->firstOr(fn() => throw new APIException(404, "Order not found!"))
            : $query->get()->whenEmpty(fn() => throw new APIException(404, "No orders found!"));
    }

    public function getAll($req)
    {
        return $this->findOrder(null, null, $req);
    }

    public function findById($id)
    {
        return $this->findOrder($id, null);
    }

    public function ownOrder($userId, $id)
    {
        return $this->findOrder($id, $userId);
    }

    public function ownOrders($userId)
    {
        return $this->findOrder(null, $userId);
    }

    public function create($data)
    {
        return Order::create([
            'order_code' => Str::uuid(),
            'user_id' => $data['user_id'],
            'cart_ids' => json_encode($data['cart_ids']),
            'total_price' => $data['total_price'],
            'vat' => $data['vat'],
            'shipping_fee' => $data['shipping_fee'],
            'final_total' => $data['final_total'],
            'is_paid' => $data['is_paid'] ?? false,
            'is_canceled' => $data['is_canceled'] ?? false,
        ]);
    }

    public function update($id, $data)
    {
        $order = $this->findById($id);

        if (isset($data['role']) && $data['role'] === 'Admin') {
            if (isset($data['is_paid'])) {
                $order->is_paid = $data['is_paid'];
            }
        } else {
            $order->is_canceled = !$order->is_canceled;
        }

        $order->save();
        return $order;
    }

    public function delete($id)
    {
        $order = $this->findById($id);
        $order->delete();
        return true;
    }
}
