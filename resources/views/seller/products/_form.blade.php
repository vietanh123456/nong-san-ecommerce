@php
    $variantRows = old('variants');

    if ($variantRows === null && isset($product)) {
        $variantRows = $product->variants
            ->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name ?: $variant->display_name,
                'image' => $variant->image,
                'unit_id' => $variant->unit_id,
                'sku' => $variant->sku,
                'quantity' => $variant->quantity,
                'price' => $variant->price,
                'stock' => $variant->stock,
                'status' => $variant->status,
            ])
            ->toArray();
    }

    if (empty($variantRows)) {
        $variantRows = [[
            'name' => '',
            'image' => null,
            'unit_id' => '',
            'sku' => '',
            'quantity' => '',
            'price' => '',
            'stock' => 0,
            'status' => true,
        ]];
    }
@endphp

<div class="form-group">
    <label for="category_id">Danh mục *</label>

    <select id="category_id" name="category_id" required>
        <option value="">-- Chọn danh mục --</option>

        @foreach ($categories as $category)
            <option
                value="{{ $category->id }}"
                @selected(
                    old(
                        'category_id',
                        $product->category_id ?? ''
                    ) == $category->id
                )
            >
                {{ $category->name }}
            </option>
        @endforeach
    </select>

    @error('category_id')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="name">Tên sản phẩm *</label>

    <input
        id="name"
        name="name"
        type="text"
        value="{{ old('name', $product->name ?? '') }}"
        required
    >

    @error('name')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="description">Mô tả</label>

    <textarea
        id="description"
        name="description"
        rows="5"
    >{{ old('description', $product->description ?? '') }}</textarea>

    @error('description')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="origin">Xuất xứ</label>

    <input
        id="origin"
        name="origin"
        type="text"
        value="{{ old('origin', $product->origin ?? '') }}"
        placeholder="Ví dụ: Cao Phong, Hòa Bình"
    >

    @error('origin')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="image">Ảnh chung của sản phẩm</label>

    <input
        id="image"
        name="image"
        type="file"
        accept=".jpg,.jpeg,.png,.webp"
    >

    <small>
        Dùng khi phân loại không có ảnh riêng.
        Định dạng JPG, JPEG, PNG hoặc WEBP; tối đa 2 MB.
    </small>

    @if (isset($product) && $product->image)
        <div style="margin-top: 12px;">
            <img
                src="{{ asset('storage/' . $product->image) }}"
                alt="{{ $product->name }}"
                class="product-image"
            >
        </div>
    @endif

    @error('image')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <input type="hidden" name="status" value="0">

    <label>
        <input
            type="checkbox"
            name="status"
            value="1"
            style="width: auto;"
            @checked(
                old(
                    'status',
                    isset($product) ? $product->status : true
                )
            )
        >
        Cho phép hiển thị và bán sản phẩm
    </label>
</div>

<div class="page-heading">
    <div>
        <h2>Phân loại sản phẩm</h2>

        <p>
            Mỗi phân loại có thể có ảnh, giá và tồn kho riêng.
        </p>
    </div>

    <button type="button" class="btn" id="add-variant">
        Thêm phân loại
    </button>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Tên phân loại *</th>
                <th>Ảnh phân loại</th>
                <th>SKU *</th>
                <th>Đơn vị</th>
                <th>Quy cách</th>
                <th>Giá *</th>
                <th>Tồn kho *</th>
                <th>Đang bán</th>
                <th></th>
            </tr>
        </thead>

        <tbody id="variant-list">
            @foreach ($variantRows as $index => $variant)
                @php
                    $existingVariant = null;

                    if (
                        isset($product) &&
                        !empty($variant['id'])
                    ) {
                        $existingVariant = $product->variants
                            ->firstWhere(
                                'id',
                                (int) $variant['id']
                            );
                    }

                    $existingVariantImage =
                        $existingVariant?->image
                        ?? ($variant['image'] ?? null);
                @endphp

                <tr class="variant-row">
                    <td>
                        @if (!empty($variant['id']))
                            <input
                                type="hidden"
                                name="variants[{{ $index }}][id]"
                                value="{{ $variant['id'] }}"
                            >
                        @endif

                        <input
                            type="text"
                            name="variants[{{ $index }}][name]"
                            value="{{ $variant['name'] ?? '' }}"
                            placeholder="Ví dụ: Hũ 300g"
                            required
                        >

                        @error("variants.$index.name")
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </td>

                    <td style="min-width: 190px;">
                        <input
                            type="file"
                            name="variants[{{ $index }}][image]"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        @if ($existingVariantImage)
                            <div style="margin-top: 8px;">
                                <img
                                    src="{{ asset('storage/' . $existingVariantImage) }}"
                                    alt="{{ $variant['name'] ?? 'Ảnh phân loại' }}"
                                    style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;"
                                >

                                <input
                                    type="hidden"
                                    name="variants[{{ $index }}][remove_image]"
                                    value="0"
                                >

                                <label
                                    style="display: block; margin-top: 6px; font-size: 12px;"
                                >
                                    <input
                                        type="checkbox"
                                        name="variants[{{ $index }}][remove_image]"
                                        value="1"
                                        style="width: auto;"
                                    >
                                    Xóa ảnh hiện tại
                                </label>
                            </div>
                        @endif

                        @error("variants.$index.image")
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </td>

                    <td>
                        <input
                            type="text"
                            name="variants[{{ $index }}][sku]"
                            value="{{ $variant['sku'] ?? '' }}"
                            placeholder="VD: MATONG-HU300"
                            required
                        >

                        @error("variants.$index.sku")
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </td>

                    <td>
                        <select name="variants[{{ $index }}][unit_id]">
                            <option value="">
                                -- Không bắt buộc --
                            </option>

                            @foreach ($units as $unit)
                                <option
                                    value="{{ $unit->id }}"
                                    @selected(
                                        ($variant['unit_id'] ?? '')
                                        == $unit->id
                                    )
                                >
                                    {{ $unit->name }}
                                    ({{ $unit->symbol }})
                                </option>
                            @endforeach
                        </select>

                        @error("variants.$index.unit_id")
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </td>

                    <td>
                        <input
                            type="number"
                            name="variants[{{ $index }}][quantity]"
                            value="{{ $variant['quantity'] ?? '' }}"
                            min="0.01"
                            step="0.01"
                            placeholder="Tùy chọn"
                        >

                        @error("variants.$index.quantity")
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </td>

                    <td>
                        <input
                            type="number"
                            name="variants[{{ $index }}][price]"
                            value="{{ $variant['price'] ?? '' }}"
                            min="0"
                            step="1000"
                            required
                        >

                        @error("variants.$index.price")
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </td>

                    <td>
                        <input
                            type="number"
                            name="variants[{{ $index }}][stock]"
                            value="{{ $variant['stock'] ?? 0 }}"
                            min="0"
                            step="1"
                            required
                        >

                        @error("variants.$index.stock")
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </td>

                    <td>
                        <input
                            type="hidden"
                            name="variants[{{ $index }}][status]"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="variants[{{ $index }}][status]"
                            value="1"
                            style="width: auto;"
                            @checked($variant['status'] ?? true)
                        >
                    </td>

                    <td>
                        <button
                            type="button"
                            class="btn btn-danger remove-variant"
                        >
                            Xóa
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="actions" style="margin-top: 24px;">
    <button type="submit" class="btn">
        {{ $submitLabel }}
    </button>

    <a
        href="{{ route('seller.products.index') }}"
        class="btn btn-secondary"
    >
        Hủy
    </a>
</div>

<template id="variant-template">
    <tr class="variant-row">
        <td>
            <input
                type="text"
                name="variants[__INDEX__][name]"
                placeholder="Ví dụ: Hộp 4 bánh"
                required
            >
        </td>

        <td style="min-width: 190px;">
            <input
                type="file"
                name="variants[__INDEX__][image]"
                accept=".jpg,.jpeg,.png,.webp"
            >
        </td>

        <td>
            <input
                type="text"
                name="variants[__INDEX__][sku]"
                placeholder="VD: BANH-HOP4"
                required
            >
        </td>

        <td>
            <select name="variants[__INDEX__][unit_id]">
                <option value="">
                    -- Không bắt buộc --
                </option>

                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">
                        {{ $unit->name }}
                        ({{ $unit->symbol }})
                    </option>
                @endforeach
            </select>
        </td>

        <td>
            <input
                type="number"
                name="variants[__INDEX__][quantity]"
                min="0.01"
                step="0.01"
                placeholder="Tùy chọn"
            >
        </td>

        <td>
            <input
                type="number"
                name="variants[__INDEX__][price]"
                min="0"
                step="1000"
                required
            >
        </td>

        <td>
            <input
                type="number"
                name="variants[__INDEX__][stock]"
                value="0"
                min="0"
                step="1"
                required
            >
        </td>

        <td>
            <input
                type="hidden"
                name="variants[__INDEX__][status]"
                value="0"
            >

            <input
                type="checkbox"
                name="variants[__INDEX__][status]"
                value="1"
                style="width: auto;"
                checked
            >
        </td>

        <td>
            <button
                type="button"
                class="btn btn-danger remove-variant"
            >
                Xóa
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    const variantList = document.getElementById('variant-list');
    const variantTemplate = document.getElementById('variant-template');
    const addVariantButton = document.getElementById('add-variant');

    let nextVariantIndex = {{ count($variantRows) }};

    addVariantButton.addEventListener('click', function () {
        const html = variantTemplate.innerHTML.replaceAll(
            '__INDEX__',
            nextVariantIndex
        );

        variantList.insertAdjacentHTML('beforeend', html);
        nextVariantIndex++;
    });

    variantList.addEventListener('click', function (event) {
        if (
            !event.target.classList.contains(
                'remove-variant'
            )
        ) {
            return;
        }

        const rows = variantList.querySelectorAll(
            '.variant-row'
        );

        if (rows.length <= 1) {
            alert(
                'Sản phẩm phải có ít nhất một phân loại.'
            );

            return;
        }

        event.target.closest('.variant-row').remove();
    });
</script>
@endpush