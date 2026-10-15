@if ($product->image)
    <img
        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}"
        alt="{{ $product->name }}"
        class="h-full w-full object-contain"
        onerror="this.hidden = true; this.nextElementSibling.hidden = false;"
    >
@endif

<div
    @if ($product->image) hidden @endif
    class="flex h-full w-full flex-col items-center justify-center gap-2 text-sm text-gray-500"
    role="img"
    aria-label="Chưa có ảnh cho {{ $product->name }}"
>
    <svg
        class="h-10 w-10 text-gray-400"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.5"
        aria-hidden="true"
    >
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5 8.25 11l3.75 4 2.25-2.5L21 19" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h15A1.5 1.5 0 0 1 21 6v12a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18V6a1.5 1.5 0 0 1 1.5-1.5Z" />
        <circle cx="8.25" cy="8.25" r="1.25" />
    </svg>
    <span>Chưa có ảnh</span>
</div>
