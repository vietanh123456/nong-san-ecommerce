@extends('layouts.seller')

@section('title', 'Sửa sản phẩm')

@section('content')
    <div class="card">
        <div class="page-heading">
            <div>
                <h1>Sửa sản phẩm</h1>
                <p>Cập nhật thông tin, giá bán và tồn kho.</p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('seller.products.update', $product) }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')

            @include('seller.products._form', [
                'submitLabel' => 'Lưu thay đổi',
                'product' => $product,
            ])
        </form>
    </div>
@endsection