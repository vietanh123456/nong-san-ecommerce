@extends('layouts.seller')

@section('title', isset($batch) ? 'Sửa lô hàng' : 'Tạo lô hàng')

@section('content')
<div class="card">
    <div class="page-heading"><h1>{{ isset($batch) ? 'Sửa lô hàng' : 'Tạo lô hàng' }}</h1><a class="btn btn-secondary" href="{{ route('seller.batches.index') }}">Quay lại</a></div>
    <form method="POST" action="{{ isset($batch) ? route('seller.batches.update', $batch) : route('seller.batches.store') }}">
        @csrf @isset($batch) @method('PUT') @endisset
        <div class="form-group"><label for="product_id">Sản phẩm</label><select id="product_id" name="product_id" required><option value="">Chọn sản phẩm</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(old('product_id', $batch->product_id ?? '') == $product->id)>{{ $product->name }}</option>@endforeach</select></div>
        <div class="form-group"><label for="batch_code">Mã lô</label><input id="batch_code" name="batch_code" value="{{ old('batch_code', $batch->batch_code ?? '') }}" placeholder="LO-CAM-001" required></div>
        <div class="form-group"><label for="origin">Xuất xứ</label><input id="origin" name="origin" value="{{ old('origin', $batch->origin ?? '') }}" required></div>
        <div class="form-group"><label for="producer">Nhà sản xuất</label><input id="producer" name="producer" value="{{ old('producer', $batch->producer ?? '') }}"></div>
        <div class="form-group"><label for="harvest_date">Ngày thu hoạch</label><input id="harvest_date" type="date" name="harvest_date" value="{{ old('harvest_date', isset($batch) ? $batch->harvest_date?->format('Y-m-d') : '') }}" required></div>
        <div class="form-group"><label for="packaged_date">Ngày đóng gói</label><input id="packaged_date" type="date" name="packaged_date" value="{{ old('packaged_date', isset($batch) ? $batch->packaged_date?->format('Y-m-d') : '') }}" required></div>
        <div class="form-group"><label for="expiry_date">Hạn sử dụng</label><input id="expiry_date" type="date" name="expiry_date" value="{{ old('expiry_date', isset($batch) ? $batch->expiry_date?->format('Y-m-d') : '') }}"></div>
        <button class="btn" type="submit">{{ isset($batch) ? 'Lưu thay đổi' : 'Tạo lô và QR' }}</button>
    </form>
</div>
@endsection
