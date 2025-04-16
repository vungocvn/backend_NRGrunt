<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderReq;
use App\Service\extend\IServiceOrder;
use Illuminate\Http\Request;
use App\Models\Order;
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

        if ($this->hasRole(['Admin', 'Admin'])) {
            $order = $this->orderService->findById($id);
        } else {
            $order = $this->orderService->ownOrder($user->id, $id);
        }

        if ($order) {
            $order->load('user');
        }

        return $this->returnJson($order, 200, "success!");
    }


    /**
     * Display the specified resource.
     */
    public function getAll(Request $request)
    {
        $user = $this->getAuth();
        if ($this->hasRole(['Admin', 'Admin'])) {
            return $this->returnJson($this->orderService->getAll($request->all()), 200, "success!");
        }

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
    public function update(Request $request, $id)
    {
        $user = $this->getAuth();
        $data = $request->all();
        $data['role'] = $user->role;
        return $this->returnJson($this->orderService->update($id, $data), 200, "change status successfully!");
    }
    public function destroy(int $id)
    {
        $this->authorizeRole(['Admin', 'Admin']);
        return $this->returnJson($this->orderService->delete($id), 204, "success!");
    }
    public function getMyOrders(Request $request)
    {
        $user = $this->getAuth();

        $orders = Order::with(['orderDetails.product', 'user']) // 👈 include quan hệ user
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $orders,
        ]);
    }
}
