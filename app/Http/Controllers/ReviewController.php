<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReviewController extends Controller
{
    public function store(
        StoreReviewRequest $request,
        Product $product
    ): RedirectResponse {
        $user = $request->user();

        if ($user->role !== 'customer') {
            abort(
                403,
                'Chỉ khách hàng mới có thể gửi đánh giá.'
            );
        }

        $review = Review::query()
            ->where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        $oldImage = $review?->image;
        $newImage = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $request
                    ->file('image')
                    ->store('reviews', 'public');
            }

            $values = [
                'rating' => $request->integer('rating'),
                'comment' => $request->input('comment'),
                'status' => Review::STATUS_APPROVED,
                'moderated_by' => null,
                'moderated_at' => null,
            ];

            if ($newImage !== null) {
                $values['image'] = $newImage;
            }

            Review::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                ],
                $values
            );
        } catch (Throwable $exception) {
            if ($newImage !== null) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if (
            $newImage !== null &&
            $oldImage !== null &&
            $oldImage !== $newImage
        ) {
            Storage::disk('public')->delete($oldImage);
        }

        return back()->with(
            'success',
            'Đánh giá của bạn đã được đăng công khai.'
        );
    }
}