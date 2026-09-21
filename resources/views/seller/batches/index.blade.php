@extends('layouts.seller')

@section('title', 'Quản lý lô hàng')

@section('content')
<div class="card">
    <div class="page-heading">
        <div><h1>Quản lý lô hàng</h1><p>Tạo mã lô và QR truy xuất nguồn gốc.</p></div>
        <a class="btn" href="{{ route('seller.batches.create') }}">Tạo lô hàng</a>
    </div>
    <div class="table-wrapper"><table>
        <thead><tr><th>Mã lô</th><th>Sản phẩm</th><th>Thu hoạch</th><th>Hạn dùng</th><th></th></tr></thead>
        <tbody>@forelse($batches as $batch)
            <tr><td>{{ $batch->batch_code }}</td><td>{{ $batch->product->name }}</td><td>{{ $batch->harvest_date?->format('d/m/Y') }}</td><td>{{ $batch->expiry_date?->format('d/m/Y') ?? '—' }}</td><td class="actions"><a class="btn btn-secondary" href="{{ route('seller.batches.show', $batch) }}">Xem</a><a class="btn btn-warning" href="{{ route('seller.batches.edit', $batch) }}">Sửa</a></td></tr>
        @empty<tr><td colspan="5">Chưa có lô hàng nào.</td></tr>@endforelse</tbody>
    </table></div>
    <div style="margin-top: 16px">{{ $batches->links() }}</div>
</div>
@endsection
