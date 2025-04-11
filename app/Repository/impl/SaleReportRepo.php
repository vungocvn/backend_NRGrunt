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
        $revenue = Order::sum('total_price');
        $orderCount = Order::count();

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
