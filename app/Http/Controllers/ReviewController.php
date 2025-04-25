<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Order;

class ReviewController extends Controller
{
    // ✅ Gửi đánh giá sản phẩm
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Kiểm tra đã từng đánh giá sản phẩm này chưa
        $existing = Review::where('user_id', $user->id)
                          ->where('product_id', $validated['product_id'])
                          ->first();

        if ($existing) {
            return response()->json(['message' => 'Bạn đã đánh giá sản phẩm này rồi.'], 409);
        }

        // ✅ Kiểm tra đã mua và đơn đã hoàn thành chưa
        $hasPurchased = Order::where('user_id', $user->id)
            ->where('status', 'completed') // status phải là "completed" hoặc tên bạn dùng cho đơn đã xong
            ->whereHas('orderDetails', function ($q) use ($validated) {
                $q->where('product_id', $validated['product_id']);
            })
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'message' => 'Bạn chỉ có thể đánh giá sau khi đã mua và hoàn thành đơn hàng.'
            ], 403);
        }

        // ✅ Lưu đánh giá
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

    // ✅ Lấy danh sách đánh giá theo sản phẩm
    public function getByProduct($productId)
    {
        $reviews = Review::with('user:id,name')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
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
}
