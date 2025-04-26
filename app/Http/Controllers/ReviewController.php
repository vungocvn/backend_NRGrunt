<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Order;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        $existing = Review::where('user_id', $user->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Bạn đã đánh giá sản phẩm này rồi.'], 409);
        }

        $hasPurchased = Order::where('user_id', $user->id)
            ->where('is_paid', 1)
            ->where('is_confirmed', 1)
            ->where('is_canceled', 0)
            ->whereHas('orderDetails', function ($q) use ($validated) {
                $q->where('product_id', $validated['product_id']);
            })
            ->exists();


        if (!$hasPurchased) {
            return response()->json([
                'message' => 'Bạn chỉ có thể đánh giá sau khi đã mua và hoàn thành đơn hàng.'
            ], 403);
        }
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
