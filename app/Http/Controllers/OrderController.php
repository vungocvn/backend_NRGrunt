<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderReq;
use App\Service\extend\IServiceOrder;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Review;

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

        $order = Order::with(['orderDetails.product', 'user'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $products = $order->orderDetails->map(function ($detail) use ($user) {
            $hasReviewed = Review::where('user_id', $user->id)
                ->where('product_id', $detail->product_id)
                ->exists();

            return [
                'id' => $detail->id,
                'product_id' => $detail->product_id,
                'product_name' => $detail->product->name,
                'quantity' => $detail->quantity,
                'unit_price' => $detail->unit_price,
                'image' => $detail->product->image ?? null,
                'is_reviewed' => $hasReviewed,
            ];
        });

        return response()->json([
            'id' => $order->id,
            'order_code' => $order->order_code,
            'vat' => $order->vat,
            'shipping_fee' => $order->shipping_fee,
            'total_price' => $order->total_price,
            'final_total' => $order->final_total,
            'user' => [
                'name' => $order->user->name,
                'phone' => $order->user->phone,
                'address' => $order->user->address,
            ],
            'products' => $products,
        ]);
    }


    public function getAll(Request $request)
    {
        $user = $this->getAuth();
        if ($this->hasRole(['Admin', 'Admin'])) {
            return $this->returnJson($this->orderService->getAll($request->all()), 200, "success!");
        }

        return $this->returnJson($this->orderService->ownOrders($user->id), 200, "success!");
    }

    public function create(OrderReq $request)
    {
        $user = $this->getAuth();
        $request->merge(['user_id' => $user->id]);
        $data = $request->all();
        return $this->returnJson($this->orderService->create($data), 201, "created successfully!");
    }

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

        $orders = Order::with(['orderDetails.product', 'user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $orders,
        ]);
    }

    public function cancel($id)
    {
        $user = $this->getAuth();

        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return $this->returnJson(null, 404, 'Không tìm thấy đơn hàng hoặc không có quyền huỷ');
        }

        if ($order->is_canceled) {
            return $this->returnJson(null, 400, 'Đơn hàng đã bị huỷ trước đó');
        }

        $order->is_canceled = 1;
        $order->save();

        return $this->returnJson($order, 200, 'Huỷ đơn hàng thành công');
    }
}
