<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
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

            'variants.*.name' => [
                'required',
                'string',
                'max:255',
                'distinct',
            ],

            'variants.*.image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'variants.*.unit_id' => [
                'nullable',
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
                'nullable',
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

            'variants.required' => 'Sản phẩm phải có ít nhất một phân loại.',
            'variants.min' => 'Sản phẩm phải có ít nhất một phân loại.',

            'variants.*.name.required' => 'Vui lòng nhập tên phân loại.',
            'variants.*.name.max' => 'Tên phân loại không được vượt quá 255 ký tự.',
            'variants.*.name.distinct' => 'Tên phân loại không được trùng nhau.',

            'variants.*.image.image' => 'Ảnh phân loại phải là hình ảnh.',
            'variants.*.image.mimes' => 'Ảnh phân loại phải có định dạng JPG, JPEG, PNG hoặc WEBP.',
            'variants.*.image.max' => 'Ảnh phân loại không được vượt quá 2 MB.',

            'variants.*.unit_id.exists' => 'Đơn vị không hợp lệ.',

            'variants.*.sku.required' => 'Vui lòng nhập mã SKU.',
            'variants.*.sku.distinct' => 'Mã SKU không được trùng nhau.',
            'variants.*.sku.unique' => 'Mã SKU đã tồn tại.',

            'variants.*.quantity.numeric' => 'Quy cách phải là một số.',
            'variants.*.quantity.gt' => 'Quy cách phải lớn hơn 0.',

            'variants.*.price.required' => 'Vui lòng nhập giá.',
            'variants.*.price.min' => 'Giá không được là số âm.',

            'variants.*.stock.required' => 'Vui lòng nhập tồn kho.',
            'variants.*.stock.integer' => 'Tồn kho phải là số nguyên.',
            'variants.*.stock.min' => 'Tồn kho không được là số âm.',
        ];
    }
}