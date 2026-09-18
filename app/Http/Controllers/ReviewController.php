<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function store(
        StoreReviewRequest $request,
        Product $product
    ): RedirectResponse {
        abort_unless(
            $request->user()->role === 'customer',
            403,
            'Chỉ khách hàng mới có thể gửi đánh giá.'
        );

        $data = $request->validated();

        Review::updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'status' => Review::STATUS_PENDING,
                'moderated_by' => null,
                'moderated_at' => null,
            ]
        );

        return back()->with(
            'success',
            'Đánh giá đã được gửi và đang chờ kiểm duyệt.'
        );
    }

    public function moderate(
        Request $request,
        Review $review
    ): RedirectResponse {
        $user = $request->user();

        $canModerate = $user->role === 'admin'
            || (
                $user->role === 'seller'
                && $review->product->seller_id === $user->id
            );

        abort_unless(
            $canModerate,
            403,
            'Bạn không có quyền kiểm duyệt đánh giá này.'
        );

        $data = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    Review::STATUS_APPROVED,
                    Review::STATUS_REJECTED,
                ]),
            ],
        ]);

        $review->update([
            'status' => $data['status'],
            'moderated_by' => $user->id,
            'moderated_at' => now(),
        ]);

        return back()->with(
            'success',
            'Trạng thái đánh giá đã được cập nhật.'
        );
    }
}