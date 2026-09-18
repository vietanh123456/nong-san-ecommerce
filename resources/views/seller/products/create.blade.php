@extends('layouts.seller')

@section('title', 'Thêm sản phẩm')

@section('content')
    <div class="card">
        <div class="page-heading">
            <div>
                <h1>Thêm sản phẩm</h1>
                <p>Tạo sản phẩm mới và các lựa chọn bán theo đơn vị.</p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('seller.products.store') }}"
            enctype="multipart/form-data"
        >
            @csrf

            @include('seller.products._form', [
                'submitLabel' => 'Thêm sản phẩm',
            ])
        </form>
    </div>
@endsection