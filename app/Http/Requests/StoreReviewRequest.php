<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],
            'comment' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_name.string' => 'Tên người đánh giá không hợp lệ.',
            'guest_name.max' => 'Tên người đánh giá không được vượt quá 100 ký tự.',
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao phải là số nguyên.',
            'rating.between' => 'Số sao phải từ 1 đến 5.',
            'comment.string' => 'Bình luận không hợp lệ.',
            'comment.max' => 'Bình luận không được vượt quá 1000 ký tự.',
        ];
    }
}
