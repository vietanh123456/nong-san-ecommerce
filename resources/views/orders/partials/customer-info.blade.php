@php($customer = $order->customer_snapshot ?? [])
<div style="background:white;border:1px solid #ddd;border-radius:10px;padding:20px;margin-bottom:20px;overflow-wrap:anywhere;">
    <h3>Thông tin khách hàng và nhận hàng</h3>
    <p><strong>Khách hàng:</strong> {{ $customer['customer_name'] ?? $order->user?->name ?? 'Chưa có thông tin' }}
        @if($order->user_id === null) (Mua không cần tài khoản) @endif
    </p>
    <p><strong>Email:</strong> {{ $customer['email'] ?? $order->user?->email ?? 'Chưa có thông tin' }}</p>
    <p><strong>Người nhận:</strong> {{ $customer['recipient_name'] ?? $order->address?->recipient_name ?? 'Chưa có thông tin' }}</p>
    <p><strong>Số điện thoại:</strong> {{ $customer['phone'] ?? $order->address?->phone ?? 'Chưa có thông tin' }}</p>
    <p><strong>Địa chỉ:</strong> {{ $customer['address_detail'] ?? $order->address?->address_detail ?? 'Chưa có thông tin' }}</p>
    <p><strong>Ghi chú:</strong> {{ $order->note ?: 'Không có ghi chú' }}</p>
</div>
