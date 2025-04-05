<?php

namespace  App\Service\impl;

use App\Exceptions\APIException;
use App\Mail\OrderNotifi;
use App\Repository\extend\ICartRepo;
use App\Repository\extend\IDetailOrderRepo;
use App\Repository\extend\IOrderRepo;
use App\Repository\extend\IProductRepo;
use App\Repository\extend\ISaleReportRepo;
use App\Repository\extend\IUserRepo;
use App\Service\extend\IServiAdminrder;
use App\Service\extend\IServiceOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderService implements IServiceOrder
{
    protected $cartRepo, $orderRepo, $productRepo, $detailOrderRepo, $userRepo, $saleReportRP;

    public function __construct(ICartRepo $cartRepo, IOrderRepo $orderRepository, IProductRepo $productRepo, IDetailOrderRepo $detailOrderRepo, IUserRepo $userRepo, ISaleReportRepo $saleReportRP)
    {
        $this->cartRepo = $cartRepo;
        $this->orderRepo = $orderRepository;
        $this->productRepo = $productRepo;
        $this->detailOrderRepo = $detailOrderRepo;
        $this->userRepo = $userRepo;
        $this->saleReportRP = $saleReportRP;
    }

    private function syncDataSR($data)
    {
        foreach ($data as $dt) {
            $this->saleReportRP->create([
                'product_id' => $dt->product_id,
                'quantity' => $dt->quantity,
                'price' => $dt->unit_price,
            ]);
        }
    }

    private function getTotalPrice($dataCart)
    {
        $totalPrice = 0;
        foreach ($dataCart as $cart) {
            $totalPrice += $cart->price * $cart->quantity;
        }

        if ($totalPrice <= 0) {
            throw new APIException(501, "Total price must be greater than zero.");
        }

        return $totalPrice;
    }

    private function syncData($dataCart = [], $idOrder, $isDelete = false)
    {
        if ($isDelete) {
            $this->detailOrderRepo->delete($idOrder);
            return;
        } else {
            foreach ($dataCart as $cart) {
                $this->detailOrderRepo->create(['order_id' => $idOrder, 'product_id' => $cart->product_id, 'quantity' => $cart->quantity, 'unit_price' => $cart->price]);
                $product = $this->productRepo->findById($cart->product_id);
                if ($product->quantity < $cart->quantity) {
                    throw new APIException(400, "Not enough stock available now!");
                }
                $product->quantity -= $cart->quantity;
                $product->save();
                $cart->delete();
            }
        }
    }

    public function getAll($req)
{
    $orders = $this->orderRepo->getAll($req);

    foreach ($orders as $order) {
        // Lấy chi tiết sản phẩm
        $details = $this->detailOrderRepo->getAll(['order_id' => $order->id]);

        $productNames = [];
        $totalQuantity = 0;

        foreach ($details as $detail) {
            $product = $this->productRepo->findById($detail->product_id);
            if ($product) {
                $productNames[] = $product->name;
            }
            $totalQuantity += $detail->quantity; // 👉 cộng dồn số lượng
        }

        $order->product_names = $productNames;
        $order->total_quantity = $totalQuantity; // 👈 gán số lượng vào order

        // Thêm thông tin khách
        $user = $this->userRepo->findById($order->user_id);
        if ($user) {
            $order->customer_info = [
                'name' => $user->name,
                'phone' => $user->phone,
                'address' => $user->address,
            ];
        }
    }

    return $orders;
}




    public function findById($id)
    {
        return $this->orderRepo->findById($id);
    }

    public function create($data)
    {
        return DB::transaction(function () use ($data) {
            $dataCart = $this->cartRepo->managerOwnCartsById($data['user_id'], $data['cart_ids']);
            $totalPrice = $this->getTotalPrice($dataCart);
            $vat = $totalPrice * 0.05;
            $shippingFee = 50000;
            $finalTotal = $totalPrice + $vat + $shippingFee;

            $data['total_price'] = $totalPrice;
            $data['vat'] = $vat;
            $data['shipping_fee'] = $shippingFee;
            $data['final_total'] = $finalTotal;

            $rs = $this->orderRepo->create($data);
            if ($rs == null) {
                throw new APIException(500, "Create order failed!");
            }

            $this->syncData($dataCart, $rs->id);
            $user = $this->userRepo->findById($data['user_id']);
            Mail::to($user->email)->queue(new OrderNotifi($rs->order_code, $user->name,  $rs->total_price, Carbon::now()->addDays(3)));
            return $rs;
        });
    }

    public function delete($id)
    {
        $this->findById($id);
        $this->orderRepo->delete($id);
        $this->syncData([], $id, true);
        return true;
    }

    public function update($id, $data)
    {
        $order = $this->findById($id);

        // Chỉ cập nhật nếu có key 'is_paid' gửi lên từ FE
        if (array_key_exists('is_paid', $data)) {
            $order->is_paid = $data['is_paid'];
        }

        // Nếu là client → chỉ được cập nhật is_canceled
        if (array_key_exists('is_canceled', $data)) {
            $order->is_canceled = $data['is_canceled'];
        }

        $order->save();
        return $order;
    }


    public function ownOrder($userId, $id)
    {
        return $this->orderRepo->ownOrder($userId, $id);
    }

    public function ownOrders($userId)
    {
        return $this->orderRepo->ownOrders($userId);
    }
}
