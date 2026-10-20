@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    <a
        href="{{ route('products.index') }}"
        class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-emerald-600 mb-6 transition"
    >
        ← Quay lại danh sách sản phẩm
    </a>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Ảnh sản phẩm --}}
        <div class="bg-gray-50 rounded-xl overflow-hidden flex items-center justify-center min-h-[320px] border border-gray-100">
            @include('products._image', ['product' => $product])
        </div>

        {{-- Thông tin sản phẩm --}}
        <div class="flex flex-col justify-between">
            <div>
                <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full uppercase">
                    {{ $product->category->name ?? 'Nông sản' }}
                </span>

                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mt-3 mb-2">
                    {{ $product->name }}
                </h1>

                {{-- Điểm đánh giá --}}
                <div class="flex items-center gap-2 mb-3">
                    <div class="text-amber-400 text-lg">
                        @for ($star = 1; $star <= 5; $star++)
                            <span>
                                {{ $star <= round($averageRating ?? 0) ? '★' : '☆' }}
                            </span>
                        @endfor
                    </div>

                    <span class="text-sm font-semibold text-gray-700">
                        {{ number_format($averageRating ?? 0, 1) }}/5
                    </span>

                    <span class="text-sm text-gray-500">
                        ({{ $reviewCount ?? 0 }} đánh giá)
                    </span>
                </div>

                <p class="text-3xl font-bold text-emerald-600 mb-4">
                    {{ number_format($product->price ?? 0, 0, ',', '.') }} đ
                </p>

                <div class="border-t border-b border-gray-100 py-4 my-4">
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">
                        Mô tả sản phẩm
                    </h3>

                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ $product->description ?? 'Sản phẩm nông sản tươi ngon, đảm bảo an toàn vệ sinh thực phẩm và nguồn gốc rõ ràng.' }}
                    </p>
                </div>

                <div class="text-sm text-gray-600 mb-6">
                    <p>
                        <strong>Xuất xứ:</strong>
                        {{ $product->origin ?? 'Đang cập nhật' }}
                    </p>

                    <p class="mt-2">
                        <strong>Tồn kho:</strong>
                        {{ $product->stock ?? 0 }}
                    </p>
                </div>
            </div>

            {{-- Thêm vào giỏ hàng --}}
            <form
                action="{{ route('cart.add', $product) }}"
                method="POST"
            >
                @csrf

                <div class="flex items-center gap-4 mb-4">
                    <label
                        for="quantity"
                        class="text-xs font-bold text-gray-700 uppercase"
                    >
                        Số lượng:
                    </label>

                    <input
                        id="quantity"
                        type="number"
                        name="quantity"
                        value="1"
                        min="1"
                        max="{{ max(1, $product->stock ?? 1) }}"
                        class="w-20 px-3 py-2 border border-gray-200 rounded-xl text-center text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl text-sm transition shadow-sm disabled:cursor-not-allowed disabled:bg-gray-400"
                    @disabled(($product->stock ?? 0) <= 0)
                >
                    @if (($product->stock ?? 0) > 0)
                        🛒 Thêm vào giỏ hàng
                    @else
                        Sản phẩm đã hết hàng
                    @endif
                </button>
            </form>

            {{-- Yêu thích --}}
            @auth
                <form
                    action="{{ route('wishlist.toggle', $product) }}"
                    method="POST"
                    class="mt-3"
                >
                    @csrf

                    <button
                        type="submit"
                        class="w-full border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold py-3 px-6 rounded-xl text-sm transition"
                    >
                        ❤️ Thêm hoặc xóa khỏi yêu thích
                    </button>
                </form>
            @else
                <a
                    href="{{ route('login') }}"
                    class="mt-3 block w-full rounded-xl border border-rose-200 py-3 px-6 text-center text-sm font-bold text-rose-600 transition hover:bg-rose-50"
                >
                    ❤️ Đăng nhập để thêm vào yêu thích
                </a>
            @endauth
        </div>
    </div>

    {{-- Gửi đánh giá --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 mt-8">
    <h2 class="text-xl font-bold text-gray-800 mb-5">
        Gửi đánh giá của bạn
    </h2>

    @if (
        !auth()->check()
        || in_array(auth()->user()->role, ['buyer', 'seller'], true)
    )
        <form
            action="{{ route('reviews.store', $product) }}"
            method="POST"
            class="space-y-4"
        >
            @csrf

            @guest
                <div>
                    <label
                        for="guest_name"
                        class="block text-sm font-bold text-gray-700 mb-2"
                    >
                        Tên hiển thị
                        <span class="font-normal text-gray-400">
                            (không bắt buộc)
                        </span>
                    </label>

                    <input
                        id="guest_name"
                        name="guest_name"
                        type="text"
                        maxlength="100"
                        value="{{ old('guest_name') }}"
                        placeholder="Để trống sẽ hiển thị Khách vãng lai"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    >

                    @error('guest_name')
                        <p class="text-sm text-red-600 mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endguest

            <div>
                <label
                    for="rating"
                    class="block text-sm font-bold text-gray-700 mb-2"
                >
                    Số sao *
                </label>

                <select
                    id="rating"
                    name="rating"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    required
                >
                    <option value="">Chọn mức đánh giá</option>
                    <option value="5" @selected(old('rating') == 5)>
                        5 sao - Rất tốt
                    </option>
                    <option value="4" @selected(old('rating') == 4)>
                        4 sao - Tốt
                    </option>
                    <option value="3" @selected(old('rating') == 3)>
                        3 sao - Bình thường
                    </option>
                    <option value="2" @selected(old('rating') == 2)>
                        2 sao - Chưa tốt
                    </option>
                    <option value="1" @selected(old('rating') == 1)>
                        1 sao - Không hài lòng
                    </option>
                </select>

                @error('rating')
                    <p class="text-sm text-red-600 mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label
                    for="comment"
                    class="block text-sm font-bold text-gray-700 mb-2"
                >
                    Bình luận
                    <span class="font-normal text-gray-400">
                        (không bắt buộc)
                    </span>
                </label>

                <textarea
                    id="comment"
                    name="comment"
                    rows="4"
                    maxlength="1000"
                    placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..."
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >{{ old('comment') }}</textarea>

                @error('comment')
                    <p class="text-sm text-red-600 mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button
                type="submit"
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl text-sm transition"
            >
                Gửi đánh giá
            </button>

            <p class="text-xs text-gray-500">
                Đánh giá sẽ được đăng công khai ngay sau khi gửi.
                Bạn không bắt buộc phải đăng nhập.
            </p>
        </form>
    @else
        <div class="bg-amber-50 text-amber-700 rounded-xl px-4 py-3 text-sm">
            Tài khoản quản trị không sử dụng chức năng đánh giá sản phẩm.
        </div>
    @endif
</div>
{{-- Danh sách đánh giá công khai --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 mt-8">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                Đánh giá sản phẩm
            </h2>

            <span class="text-sm text-gray-500">
                {{ $reviewCount ?? 0 }} đánh giá
            </span>
        </div>

        @if ($product->reviews->isNotEmpty())
            <div class="space-y-5">
                @foreach ($product->reviews as $review)
                    <div class="border-b border-gray-100 pb-5 last:border-b-0 last:pb-0">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-gray-800">
                                    {{ $review->user?->name ?? $review->guest_name ?? 'Khách vãng lai' }}
                                </p>

                                <div class="text-amber-400 mt-1">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <span>
                                            {{ $star <= $review->rating ? '★' : '☆' }}
                                        </span>
                                    @endfor
                                </div>
                            </div>

                            <span class="text-xs text-gray-400">
                                {{ $review->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        @if ($review->comment)
                            <p class="text-sm text-gray-600 leading-relaxed mt-3">
                                {{ $review->comment }}
                            </p>
                        @endif
                    @auth
                        @if (
                            $review->user_id !== null
                            && (int) $review->user_id === (int) auth()->id()
                        )
                            <form
                                action="{{ route('reviews.destroy', $review) }}"
                                method="POST"
                                class="mt-4"
                                onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá của mình?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-600 transition hover:bg-red-100"
                                >
                                    Xóa đánh giá của tôi
                                </button>
                            </form>
                        @endif
                    @endauth
                    @if ($review->seller_reply)
                        <div class="mt-4 ml-4 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-bold text-emerald-800">
                                    🏪 Phản hồi từ người bán
                                </p>

                                @if ($review->seller_replied_at)
                                    <span class="text-xs text-emerald-600">
                                        {{ $review->seller_replied_at->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-2 text-sm leading-relaxed text-gray-700">
                                {{ $review->seller_reply }}
                            </p>
                        </div>
                    @endif

                    @auth
                        @if (
                            auth()->user()->role === 'seller'
                            && (int) $product->seller_id === (int) auth()->id()
                        )
                            <div class="mt-4 ml-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
                                <form
                                    action="{{ route('reviews.reply', $review) }}"
                                    method="POST"
                                    class="space-y-3"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <label
                                        for="seller_reply_{{ $review->id }}"
                                        class="block text-sm font-bold text-gray-700"
                                    >
                                        {{ $review->seller_reply
                                            ? 'Sửa phản hồi'
                                            : 'Phản hồi đánh giá'
                                        }}
                                    </label>

                                    <textarea
                                        id="seller_reply_{{ $review->id }}"
                                        name="seller_reply"
                                        rows="3"
                                        maxlength="1000"
                                        placeholder="Nhập phản hồi của người bán..."
                                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    >{{ $review->seller_reply }}</textarea>

                                    @error('seller_reply')
                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                    <button
                                        type="submit"
                                        class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700"
                                    >
                                        {{ $review->seller_reply
                                            ? 'Cập nhật phản hồi'
                                            : 'Gửi phản hồi'
                                        }}
                                    </button>
                                </form>

                                @if ($review->seller_reply)
                                    <form
                                        action="{{ route('reviews.reply.destroy', $review) }}"
                                        method="POST"
                                        class="mt-3"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa phản hồi này?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-red-700"
                                        >
                                            Xóa phản hồi
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    @endauth
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 rounded-xl px-5 py-8 text-center">
                <div class="text-4xl mb-3">⭐</div>

                <p class="font-semibold text-gray-700">
                    Sản phẩm chưa có đánh giá.
                </p>

                <p class="text-sm text-gray-500 mt-1">
                    Hãy là người đầu tiên chia sẻ cảm nhận.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection



