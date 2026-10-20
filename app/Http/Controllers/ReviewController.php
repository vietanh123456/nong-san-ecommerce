<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(
        StoreReviewRequest $request,
        Product $product
    ): RedirectResponse {
        $user = $request->user();

        if (
            $user !== null
            && !in_array($user->role, ['buyer', 'seller'], true)
        ) {
            abort(
                403,
                'Tài khoản này không có quyền gửi đánh giá.'
            );
        }

        $reviewData = [
            'rating' => $request->integer('rating'),
            'comment' => $request->filled('comment')
                ? trim((string) $request->input('comment'))
                : null,
            'status' => Review::STATUS_APPROVED,
            'moderated_by' => null,
            'moderated_at' => null,
        ];

        if ($user !== null) {
            Review::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                ],
                array_merge($reviewData, [
                    'guest_name' => null,
                ])
            );
        } else {
            Review::create(array_merge($reviewData, [
                'product_id' => $product->id,
                'user_id' => null,
                'guest_name' => $request->filled('guest_name')
                    ? trim((string) $request->input('guest_name'))
                    : null,
            ]));
        }

        return back()->with(
            'success',
            'Đánh giá của bạn đã được đăng công khai.'
        );
    }

    public function destroy(
        Request $request,
        Review $review
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user !== null
                && $review->user_id !== null
                && (int) $review->user_id === (int) $user->id,
            403,
            'Bạn chỉ có thể xóa đánh giá của chính mình.'
        );

        $review->delete();

        return back()->with(
            'success',
            'Đánh giá của bạn đã được xóa.'
        );
    }
    public function reply(
        Request $request,
        Review $review
    ): RedirectResponse {
        $user = $request->user();
        $review->loadMissing('product');

        abort_unless(
            $user !== null
                && $user->role === 'seller'
                && (int) $review->product->seller_id === (int) $user->id,
            403,
            'Bạn không có quyền phản hồi đánh giá này.'
        );

        $validated = $request->validate([
            'seller_reply' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'seller_reply.required' => 'Vui lòng nhập nội dung phản hồi.',
            'seller_reply.string' => 'Nội dung phản hồi không hợp lệ.',
            'seller_reply.max' => 'Phản hồi không được vượt quá 1000 ký tự.',
        ]);

        $review->update([
            'seller_reply' => trim($validated['seller_reply']),
            'seller_replied_at' => now(),
        ]);

        return back()->with(
            'success',
            'Phản hồi đánh giá đã được lưu.'
        );
    }

    public function destroyReply(
        Request $request,
        Review $review
    ): RedirectResponse {
        $user = $request->user();
        $review->loadMissing('product');

        abort_unless(
            $user !== null
                && $user->role === 'seller'
                && (int) $review->product->seller_id === (int) $user->id,
            403,
            'Bạn không có quyền xóa phản hồi này.'
        );

        $review->update([
            'seller_reply' => null,
            'seller_replied_at' => null,
        ]);

        return back()->with(
            'success',
            'Phản hồi đánh giá đã được xóa.'
        );
    }
}

