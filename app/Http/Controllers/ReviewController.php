<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(
        StoreReviewRequest $request,
        Product $product
    ): RedirectResponse {
        $user = $request->user();

        if ($user->role !== 'customer') {
            abort(403, 'Chỉ khách hàng mới có thể gửi đánh giá.');
        }

        Review::updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $request->integer('rating'),
                'comment' => $request->input('comment'),
                'status' => Review::STATUS_APPROVED,
                'moderated_by' => null,
                'moderated_at' => null,
            ]
        );

        return back()->with(
            'success',
            'Đánh giá của bạn đã được đăng công khai.'
        );
    }
}