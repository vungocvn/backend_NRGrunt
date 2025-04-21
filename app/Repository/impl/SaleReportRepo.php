<?php

namespace App\Repository\impl;

use App\Models\Order;
use App\Models\SaleReport;
use App\Repository\BaseRepository;
use App\Repository\extend\ISaleReportRepo;

class SaleReportRepo extends BaseRepository implements ISaleReportRepo
{
    public function getAll($reqParam)
    {
        $orders = Order::where('is_canceled', 0)
                       ->where('is_paid', 1)
                       ->get();

        $revenue = $orders->sum('final_total'); 
        $orderCount = $orders->count();

        return response()->json([
            'status' => 200,
            'total_revenue' => $revenue,
            'total_orders' => $orderCount
        ]);
    }


    public function findById($id) {}

    public function create($data)
    {
        return SaleReport::create($data);
    }

    public function update($id, $data) {}

    public function delete($id) {}

    public function changeStatus($id) {}
}
