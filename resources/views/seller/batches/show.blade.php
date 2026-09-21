@extends('layouts.seller')

@section('title', 'Chi tiết lô '.$batch->batch_code)

@section('content')
<div class="card">
    <div class="page-heading"><h1>Lô {{ $batch->batch_code }}</h1><a class="btn btn-warning" href="{{ route('seller.batches.edit', $batch) }}">Sửa lô</a></div>
    <p><strong>Sản phẩm:</strong> {{ $batch->product->name }}</p><p><strong>Xuất xứ:</strong> {{ $batch->origin }}</p><p><strong>Nhà sản xuất:</strong> {{ $batch->producer ?? '—' }}</p>
    <p><strong>Ngày thu hoạch:</strong> {{ $batch->harvest_date?->format('d/m/Y') }}</p><p><strong>Ngày đóng gói:</strong> {{ $batch->packaged_date?->format('d/m/Y') }}</p>
    @if($batch->qr_path)<p><strong>QR truy xuất:</strong><br><img width="220" src="{{ asset('storage/'.$batch->qr_path) }}" alt="QR {{ $batch->batch_code }}"><br><a href="{{ route('trace.show', $batch->batch_code) }}" target="_blank">Mở trang truy xuất</a></p>@endif
    <hr><h2>Chứng nhận</h2>
    <form method="POST" enctype="multipart/form-data" action="{{ route('seller.batches.certificates.store', $batch) }}">@csrf
        <div class="form-group"><label>Tên chứng nhận</label><input name="name" required></div><div class="form-group"><label>File (PDF/JPG/PNG/WEBP, tối đa 5 MB)</label><input type="file" name="file" required></div><button class="btn">Tải chứng nhận</button>
    </form>
    <ul>@foreach($batch->certificates as $certificate)<li><a target="_blank" href="{{ route('seller.certificates.show', $certificate) }}">{{ $certificate->name }}</a> — {{ $certificate->status }}</li>@endforeach</ul>
    <form method="POST" action="{{ route('seller.batches.destroy', $batch) }}" style="margin-top:20px">@csrf @method('DELETE')<button class="btn btn-danger" onclick="return confirm('Xóa lô này?')">Xóa lô</button></form>
</div>
@endsection
