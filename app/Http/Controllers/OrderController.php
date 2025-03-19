<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthException;
use App\Http\Requests\OrderReq;
use App\Models\Order;
use App\Service\extend\IServiceOrder;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(IServiceOrder $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getById($id)
    {
        $user = $this->getAuth();
        // Chỉ Admin mới có quyền xem tất cả đơn hàng
        if ($this->hasRole(['Admin'])) {
            return $this->returnJson($this->orderService->findById($id), 200, "success!");
        }
        // Người dùng khác chỉ có thể xem đơn hàng của chính họ
        return $this->returnJson($this->orderService->ownOrder($user->id, $id), 200, "success!");
    }

    /**
     * Display the specified resource.
     */
    public function getAll()
    {
        $user = $this->getAuth();
        // Chỉ Admin mới có quyền xem tất cả đơn hàng
        if ($this->hasRole(['Admin'])) {
            return $this->returnJson($this->orderService->getAll("any"), 200, "success!");
        }
        // Người dùng khác chỉ có thể xem các đơn hàng của chính họ
        return $this->returnJson($this->orderService->ownOrders($user->id), 200, "success!");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(OrderReq $request)
    {
        $user = $this->getAuth();
        $request->merge(['user_id' => $user->id]);
        $data = $request->all();
        return $this->returnJson($this->orderService->create($data), 201, "created successfully!");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $user = $this->getAuth();
        $data['role'] = $user->role;
        return $this->returnJson($this->orderService->update($id, $data), 200, "change status successfully!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        // Chỉ Admin mới có quyền xóa đơn hàng
        $this->authorizeRole(['Admin']);
        return $this->returnJson($this->orderService->delete($order->id), 204, "success!");
    }
}
