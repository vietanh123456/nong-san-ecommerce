<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Tạm thời cho phép để phát triển CRUD.
        // Sẽ thay bằng kiểm tra quyền Seller khi Người 1 hoàn thành Auth.
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->where('status', true),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'origin' => [
                'nullable',
                'string',
                'max:255',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'variants' => [
                'required',
                'array',
                'min:1',
            ],

            'variants.*.unit_id' => [
                'required',
                Rule::exists('units', 'id')
                    ->where('status', true),
            ],

            'variants.*.sku' => [
                'required',
                'string',
                'max:100',
                'distinct',
                'unique:product_variants,sku',
            ],

            'variants.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'variants.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'variants.*.stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'variants.*.status' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục không hợp lệ hoặc đã bị khóa.',

            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',

            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Ảnh phải có định dạng JPG, JPEG, PNG hoặc WEBP.',
            'image.max' => 'Dung lượng ảnh không được vượt quá 2 MB.',

            'variants.required' => 'Sản phẩm phải có ít nhất một lựa chọn bán.',
            'variants.min' => 'Sản phẩm phải có ít nhất một lựa chọn bán.',

            'variants.*.unit_id.required' => 'Vui lòng chọn đơn vị.',
            'variants.*.unit_id.exists' => 'Đơn vị không hợp lệ.',

            'variants.*.sku.required' => 'Vui lòng nhập mã SKU.',
            'variants.*.sku.distinct' => 'Mã SKU không được trùng nhau.',
            'variants.*.sku.unique' => 'Mã SKU đã tồn tại.',

            'variants.*.quantity.required' => 'Vui lòng nhập khối lượng.',
            'variants.*.quantity.gt' => 'Khối lượng phải lớn hơn 0.',

            'variants.*.price.required' => 'Vui lòng nhập giá.',
            'variants.*.price.min' => 'Giá không được là số âm.',

            'variants.*.stock.required' => 'Vui lòng nhập tồn kho.',
            'variants.*.stock.integer' => 'Tồn kho phải là số nguyên.',
            'variants.*.stock.min' => 'Tồn kho không được là số âm.',
        ];
    }
}