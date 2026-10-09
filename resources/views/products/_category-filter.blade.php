@foreach ($categories as $category)
    <div class="flex items-center justify-between gap-2 py-1">
        <label
            for="category-{{ $category->id }}"
            class="flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-sm text-gray-700"
        >
            <input
                id="category-{{ $category->id }}"
                type="checkbox"
                name="categories[]"
                value="{{ $category->id }}"
                @checked($selectedCategoryIds->contains($category->id))
                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
            >
            <span class="truncate">{{ $category->name }}</span>
        </label>

        @if ($category->children->isNotEmpty())
            <details class="group shrink-0">
                <summary
                    class="flex h-7 w-7 cursor-pointer list-none items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 [&::-webkit-details-marker]:hidden"
                    aria-label="Mở danh mục con của {{ $category->name }}"
                >
                    <span class="transition group-open:rotate-180" aria-hidden="true">⌄</span>
                </summary>

                <div class="ml-2 border-l border-gray-200 pl-3">
                    @include('products._category-filter', [
                        'categories' => $category->children,
                        'selectedCategoryIds' => $selectedCategoryIds,
                    ])
                </div>
            </details>
        @endif
    </div>
@endforeach
