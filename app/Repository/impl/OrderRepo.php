<?php

namespace App\Repository\impl;

use App\Exceptions\APIException;
use App\Exceptions\AuthorizeException;
use App\Models\Order;
use App\Repository\BaseRepository;
use App\Repository\extend\IOrderRepo;
use Illuminate\Support\Str;

class OrderRepo extends BaseRepository implements IOrderRepo
{
    private function findOrder($id = null, $userId = null)
    {
        if($userId !== null) {
            $query = Order::where('user_id', $userId);
        }
        if ($id !== null) {
            $data = $query->where('id', $id)->first();
            if (!$data) {
                throw new APIException(404, "Order not found or not authorized!");
            }
            return $data;
        }

        $orders = $query->get();
        if ($orders->isEmpty()) {
            throw new APIException(404, "No orders found!");
        }

        return $orders;
    }


    public function getAll($req)
    {
        return $this->findOrder(null, $req);
    }

    public function findById($id)
    {
        return $this->findOrder($id, null);
    }

    public function ownOrder($userId, $id)
    {
        $order = $this->findOrder($id, $userId);
        return $order;
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
            'cart_ids' => $data['cart_ids'],
            'total_price' => $data['total_price'],
            'is_paid' => $data['is_paid'] ?? false,
            'is_canceled' => $data['is_canceled'] ?? false
        ]);
    }

    public function update($id, $data)
    {
        $order = $this->findById($id);
        if ($data['role'] === 'CEO' || $data['role'] === 'Admin') {
            $order->is_paid = !$order->is_paid;
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
