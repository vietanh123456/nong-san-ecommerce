@extends('layouts.app')

@section('content')
@php
    $firstAvailableVariant = $product->variants
        ->first(fn ($variant) => $variant->stock > 0);

    $selectedVariantId = old(
        'variant_id',
        $firstAvailableVariant?->id
    );

    $selectedVariant = $product->variants
        ->firstWhere('id', (int) $selectedVariantId);

    $displayPrice = $selectedVariant?->price
        ?? $product->price
        ?? 0;

    $displayStock = $selectedVariant?->stock ?? 0;

    $displayImage = $selectedVariant?->image
        ?: $product->image;
@endphp

<div class="max-w-5xl mx-auto py-8 px-4">
    <a
        href="{{ route('products.index') }}"
        class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-emerald-600 mb-6 transition"
    >
        ← Quay lại danh sách sản phẩm
    </a>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Ảnh sản phẩm hoặc phân loại --}}
        <div class="bg-gray-50 rounded-xl overflow-hidden flex items-center justify-center min-h-[320px] border border-gray-100">
            <img
                id="product-main-image"
                src="{{ $displayImage ? asset('storage/' . $displayImage) : '' }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover {{ $displayImage ? '' : 'hidden' }}"
            >

            <span
                id="product-image-placeholder"
                class="text-8xl {{ $displayImage ? 'hidden' : '' }}"
            >
                🥑
            </span>
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

                <p
                    id="variant-price"
                    class="text-3xl font-bold text-emerald-600 mb-4"
                >
                    {{ number_format($displayPrice, 0, ',', '.') }} đ
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
                        <strong>Tồn kho phân loại:</strong>

                        <span id="variant-stock">
                            {{ $displayStock }}
                        </span>
                    </p>
                </div>
            </div>

            {{-- Thêm vào giỏ hàng --}}
            @if ($product->variants->isNotEmpty())
                <form
                    action="{{ route('cart.add', $product) }}"
                    method="POST"
                >
                    @csrf

                    <div class="mb-4">
                        <label
                            for="variant_id"
                            class="block text-xs font-bold text-gray-700 uppercase mb-2"
                        >
                            Phân loại sản phẩm:
                        </label>

                        <select
                            id="variant_id"
                            name="variant_id"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            required
                        >
                            <option value="">
                                -- Chọn phân loại --
                            </option>

                            @foreach ($product->variants as $variant)
                                <option
                                    value="{{ $variant->id }}"
                                    data-price="{{ (float) $variant->price }}"
                                    data-stock="{{ $variant->stock }}"
                                    data-image="{{ $variant->image
                                        ? asset('storage/' . $variant->image)
                                        : ($product->image
                                            ? asset('storage/' . $product->image)
                                            : '') }}"
                                    @selected(
                                        (int) $selectedVariantId ===
                                        $variant->id
                                    )
                                    @disabled($variant->stock <= 0)
                                >
                                    {{ $variant->display_name }}
                                    — {{ number_format($variant->price, 0, ',', '.') }} đ

                                    @if ($variant->stock <= 0)
                                        (Hết hàng)
                                    @else
                                        (Còn {{ $variant->stock }})
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        @error('variant_id')
                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

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
                            value="{{ old('quantity', 1) }}"
                            min="1"
                            max="{{ max(1, $displayStock) }}"
                            class="w-24 px-3 py-2 border border-gray-200 rounded-xl text-center text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            required
                        >
                    </div>

                    @error('quantity')
                        <p class="text-sm text-red-600 mb-3">
                            {{ $message }}
                        </p>
                    @enderror

                    @error('product')
                        <p class="text-sm text-red-600 mb-3">
                            {{ $message }}
                        </p>
                    @enderror

                    <button
                        id="add-to-cart-button"
                        type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl text-sm transition shadow-sm disabled:cursor-not-allowed disabled:bg-gray-400"
                        @disabled(!$firstAvailableVariant)
                    >
                        @if ($firstAvailableVariant)
                            🛒 Thêm vào giỏ hàng
                        @else
                            Sản phẩm đã hết hàng
                        @endif
                    </button>
                </form>
            @else
                <div class="bg-gray-100 text-gray-600 rounded-xl px-4 py-4 text-sm font-semibold text-center">
                    Sản phẩm chưa có phân loại đang bán.
                </div>
            @endif

            {{-- Yêu thích --}}
            @auth
                @if (auth()->user()->role === 'customer')
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
                @endif
            @endauth
        </div>
    </div>

    {{-- Gửi đánh giá --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 mt-8">
        <h2 class="text-xl font-bold text-gray-800 mb-5">
            Gửi đánh giá của bạn
        </h2>

        @auth
            @if (auth()->user()->role === 'customer')
                <form
                    action="{{ route('reviews.store', $product) }}"
                    method="POST"
                    class="space-y-4"
                >
                    @csrf

                    <div>
                        <label
                            for="rating"
                            class="block text-sm font-bold text-gray-700 mb-2"
                        >
                            Số sao
                        </label>

                        <select
                            id="rating"
                            name="rating"
                            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            required
                        >
                            <option value="">
                                Chọn mức đánh giá
                            </option>

                            @for ($rating = 5; $rating >= 1; $rating--)
                                <option
                                    value="{{ $rating }}"
                                    @selected(old('rating') == $rating)
                                >
                                    {{ $rating }} sao
                                </option>
                            @endfor
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
                        Đánh giá của bạn sẽ được đăng công khai sau khi gửi.
                    </p>
                </form>
            @else
                <div class="bg-amber-50 text-amber-700 rounded-xl px-4 py-3 text-sm">
                    Chỉ tài khoản khách hàng mới có thể gửi đánh giá.
                </div>
            @endif
        @else
            <div class="bg-gray-50 rounded-xl px-4 py-4 text-sm text-gray-600">
                Bạn cần

                <a
                    href="{{ route('login') }}"
                    class="font-bold text-emerald-600 hover:text-emerald-700"
                >
                    đăng nhập
                </a>

                để gửi đánh giá.
            </div>
        @endauth
    </div>

    {{-- Danh sách đánh giá --}}
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
                                    {{ $review->user->name ?? 'Khách hàng' }}
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
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 rounded-xl px-5 py-8 text-center">
                <div class="text-4xl mb-3">⭐</div>

                <p class="font-semibold text-gray-700">
                    Sản phẩm chưa có đánh giá.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    const variantSelect = document.getElementById('variant_id');
    const priceElement = document.getElementById('variant-price');
    const stockElement = document.getElementById('variant-stock');
    const quantityInput = document.getElementById('quantity');
    const addButton = document.getElementById('add-to-cart-button');

    const productImage = document.getElementById(
        'product-main-image'
    );

    const productImagePlaceholder = document.getElementById(
        'product-image-placeholder'
    );

    function formatCurrency(value) {
        return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
    }

    function updateSelectedVariant() {
        if (!variantSelect) {
            return;
        }

        const option = variantSelect.options[
            variantSelect.selectedIndex
        ];

        if (!option || !option.value) {
            priceElement.textContent = 'Vui lòng chọn phân loại';
            stockElement.textContent = '0';
            quantityInput.max = 1;
            addButton.disabled = true;

            return;
        }

        const price = Number(option.dataset.price || 0);
        const stock = Number(option.dataset.stock || 0);
        const imageUrl = option.dataset.image || '';

        priceElement.textContent = formatCurrency(price);
        stockElement.textContent = stock;
        quantityInput.max = Math.max(1, stock);

        if (Number(quantityInput.value) > stock) {
            quantityInput.value = Math.max(1, stock);
        }

        if (imageUrl) {
            productImage.src = imageUrl;
            productImage.classList.remove('hidden');
            productImagePlaceholder.classList.add('hidden');
        } else {
            productImage.removeAttribute('src');
            productImage.classList.add('hidden');
            productImagePlaceholder.classList.remove('hidden');
        }

        addButton.disabled = stock <= 0;
        addButton.textContent = stock > 0
            ? '🛒 Thêm vào giỏ hàng'
            : 'Sản phẩm đã hết hàng';
    }

    if (variantSelect) {
        variantSelect.addEventListener(
            'change',
            updateSelectedVariant
        );

        updateSelectedVariant();
    }
</script>
@endpush