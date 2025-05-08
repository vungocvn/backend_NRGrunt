<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Order;

class ReviewController extends Controller
{
    // Phương thức lưu đánh giá sản phẩm
    public function store(Request $request)
    {
        $user = auth()->user();  // Lấy người dùng hiện tại

        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Kiểm tra xem người dùng đã đánh giá sản phẩm này chưa
        $existing = Review::where('user_id', $user->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Bạn đã đánh giá sản phẩm này rồi.'], 409);
        }

        // Kiểm tra người dùng đã mua sản phẩm này chưa
        $hasPurchased = Order::where('user_id', $user->id)
            ->where('is_paid', 1)
            ->where('is_confirmed', 1)
            ->where('is_canceled', 0)
            ->whereHas('orderDetails', function ($q) use ($validated) {
                $q->where('product_id', $validated['product_id']);
            })
            ->exists();

        if (!$hasPurchased) {
            return response()->json(['message' => 'Bạn chỉ có thể đánh giá sau khi đã mua và hoàn thành đơn hàng.'], 403);
        }

        // Tạo mới đánh giá
        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'message' => 'Đánh giá thành công!',
            'review' => $review
        ]);
    }

    public function getByProduct($productId)
    {
        $reviews = Review::with('user:id,name')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $reviews = $reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'user_name' => $review->user->name,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json($reviews);
    }
    public function getByProductIds(Request $request)
    {
        $productIds = $request->input('product_ids');

        if (empty($productIds)) {
            return response()->json(['message' => 'Không có sản phẩm nào được chọn.'], 400);
        }

        $reviews = Review::whereIn('product_id', $productIds)
            ->with('user:id,name')
            ->get()
            ->groupBy('product_id');

        return response()->json($reviews);
    }
}
