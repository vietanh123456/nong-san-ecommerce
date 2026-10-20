<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $palettes = [
            'trai-cay' => ['#fff7ed', '#fb923c', '#c2410c'],
            'rau-cu' => ['#f0fdf4', '#22c55e', '#166534'],
            'gao-va-ngu-coc' => ['#fffbeb', '#eab308', '#854d0e'],
            'ca-phe-va-tra' => ['#fefce8', '#92400e', '#451a03'],
            'mat-ong' => ['#fffbeb', '#f59e0b', '#92400e'],
            'gia-vi-va-dac-san' => ['#fff1f2', '#e11d48', '#881337'],
            'hat-va-qua-kho' => ['#fef3c7', '#b45309', '#78350f'],
            'thuc-pham-kho' => ['#fafaf9', '#78716c', '#292524'],
            'nam' => ['#faf5ff', '#a855f7', '#6b21a8'],
            'banh-va-dac-san' => ['#fff7ed', '#f97316', '#9a3412'],
            'thuy-hai-san' => ['#eff6ff', '#3b82f6', '#1e40af'],
            'thit-va-san-pham-che-bien' => ['#fef2f2', '#ef4444', '#991b1b'],
            'do-uong' => ['#ecfeff', '#06b6d4', '#155e75'],
            'san-pham-huu-co' => ['#ecfdf5', '#10b981', '#065f46'],
            'qua-bieu-va-combo' => ['#fdf4ff', '#d946ef', '#86198f'],
        ];

        $products = Product::query()
            ->with('category')
            ->orderBy('id')
            ->get();

        foreach ($products as $product) {
            $categorySlug = $product->category?->slug ?? 'nong-san';

            [$background, $accent, $dark] = $palettes[$categorySlug]
                ?? ['#ecfdf5', '#10b981', '#065f46'];

            $name = htmlspecialchars(
                $product->name,
                ENT_QUOTES | ENT_XML1,
                'UTF-8'
            );

            $categoryName = htmlspecialchars(
                $product->category?->name ?? 'Nông sản Việt',
                ENT_QUOTES | ENT_XML1,
                'UTF-8'
            );

            $origin = htmlspecialchars(
                $product->origin ?: 'Việt Nam',
                ENT_QUOTES | ENT_XML1,
                'UTF-8'
            );

            $words = preg_split('/\s+/u', trim($product->name)) ?: [];
            $middle = max(1, (int) ceil(count($words) / 2));

            $lineOne = htmlspecialchars(
                implode(' ', array_slice($words, 0, $middle)),
                ENT_QUOTES | ENT_XML1,
                'UTF-8'
            );

            $lineTwo = htmlspecialchars(
                implode(' ', array_slice($words, $middle)),
                ENT_QUOTES | ENT_XML1,
                'UTF-8'
            );

            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="900" viewBox="0 0 1200 900">
    <defs>
        <linearGradient id="background" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{$background}"/>
            <stop offset="100%" stop-color="#ffffff"/>
        </linearGradient>
        <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="18" stdDeviation="22" flood-color="{$dark}" flood-opacity="0.16"/>
        </filter>
    </defs>

    <rect width="1200" height="900" rx="48" fill="url(#background)"/>

    <circle cx="1020" cy="120" r="190" fill="{$accent}" opacity="0.10"/>
    <circle cx="110" cy="800" r="230" fill="{$accent}" opacity="0.08"/>

    <g filter="url(#shadow)">
        <rect x="250" y="105" width="700" height="410" rx="64" fill="#ffffff"/>
    </g>

    <ellipse cx="600" cy="438" rx="235" ry="42" fill="{$dark}" opacity="0.10"/>

    <g transform="translate(600 305)">
        <path d="M0 125 C-155 78 -205 -35 -142 -128 C-30 -120 44 -58 0 125Z"
              fill="{$accent}" opacity="0.92"/>
        <path d="M0 125 C145 85 204 -26 150 -118 C35 -116 -38 -48 0 125Z"
              fill="{$dark}" opacity="0.82"/>
        <path d="M0 120 C-7 45 5 -43 80 -117"
              fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" opacity="0.85"/>
        <circle cx="0" cy="125" r="72" fill="{$accent}"/>
        <circle cx="-23" cy="101" r="18" fill="#ffffff" opacity="0.40"/>
    </g>

    <rect x="96" y="72" width="250" height="54" rx="27" fill="{$accent}"/>
    <text x="221" y="108" text-anchor="middle"
          font-family="Arial, sans-serif" font-size="24" font-weight="700"
          fill="#ffffff">{$categoryName}</text>

    <text x="600" y="625" text-anchor="middle"
          font-family="Arial, sans-serif" font-size="52" font-weight="800"
          fill="{$dark}">{$lineOne}</text>

    <text x="600" y="690" text-anchor="middle"
          font-family="Arial, sans-serif" font-size="52" font-weight="800"
          fill="{$dark}">{$lineTwo}</text>

    <text x="600" y="762" text-anchor="middle"
          font-family="Arial, sans-serif" font-size="27"
          fill="#64748b">Nguồn gốc: {$origin}</text>

    <text x="600" y="824" text-anchor="middle"
          font-family="Arial, sans-serif" font-size="23" font-weight="700"
          letter-spacing="4" fill="{$accent}">NÔNG SẢN VIỆT</text>

    <title>{$name}</title>
</svg>
SVG;

            $path = 'products/demo/'
                .$product->id.'-'.Str::slug($product->name).'.svg';

            $uploaded = Storage::disk('supabase')->put(
                $path,
                $svg,
                [
                    'ContentType' => 'image/svg+xml',
                    'CacheControl' => 'public, max-age=31536000',
                ]
            );

            if (!$uploaded) {
                throw new RuntimeException(
                    "Không thể tải ảnh cho sản phẩm #{$product->id}"
                );
            }

            $oldImage = $product->image;

            $product->update([
                'image' => $path,
            ]);

            if (
                $oldImage
                && $oldImage !== $path
                && Str::startsWith($oldImage, 'products/demo/')
            ) {
                Storage::disk('supabase')->delete($oldImage);
            }
        }

        $this->command?->info(
            'Đã tạo ảnh cho '.$products->count().' sản phẩm.'
        );
    }
}
