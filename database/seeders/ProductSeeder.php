<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoSellerSeeder::class,
            CategorySeeder::class,
            UnitSeeder::class,
        ]);

        $requiredUnits = [
            ['name' => 'Gram', 'symbol' => 'g'],
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Chai', 'symbol' => 'chai'],
            ['name' => 'Hũ', 'symbol' => 'hũ'],
            ['name' => 'Hộp', 'symbol' => 'hộp'],
            ['name' => 'Gói', 'symbol' => 'gói'],
            ['name' => 'Túi', 'symbol' => 'túi'],
            ['name' => 'Bó', 'symbol' => 'bó'],
            ['name' => 'Quả', 'symbol' => 'quả'],
            ['name' => 'Trái', 'symbol' => 'trái'],
            ['name' => 'Combo', 'symbol' => 'combo'],
        ];

        foreach ($requiredUnits as $requiredUnit) {
            Unit::updateOrCreate(
                ['symbol' => $requiredUnit['symbol']],
                [
                    'name' => $requiredUnit['name'],
                    'description' => 'Đơn vị sử dụng cho sản phẩm.',
                    'status' => true,
                ]
            );
        }
        $shopEmails = [
            'nongtraixanh@example.com',
            'dacsanviet@example.com',
            'nongsanmientay@example.com',
        ];

        $shops = User::query()
            ->whereIn('email', $shopEmails)
            ->get()
            ->keyBy('email');

        foreach ($shopEmails as $email) {
            if (!$shops->has($email)) {
                throw new RuntimeException(
                    "Không tìm thấy shop: {$email}. Hãy chạy DemoSellerSeeder trước."
                );
            }
        }

        $categories = Category::query()
            ->where('status', true)
            ->get()
            ->keyBy('slug');

        $units = Unit::query()
            ->where('status', true)
            ->get()
            ->keyBy('symbol');

        $products = [
            // Nông Trại Xanh Đà Lạt
            ['shop' => 0, 'category' => 'trai-cay', 'name' => 'Dâu Tây Đà Lạt', 'origin' => 'Đà Lạt, Lâm Đồng', 'price' => 145000, 'stock' => 45, 'unit' => 'kg', 'variant' => 'Hộp 1kg', 'sku' => 'NTX-DAU-1KG'],
            ['shop' => 0, 'category' => 'trai-cay', 'name' => 'Hồng Giòn Đà Lạt', 'origin' => 'Đà Lạt, Lâm Đồng', 'price' => 68000, 'stock' => 70, 'unit' => 'kg', 'variant' => 'Túi 1kg', 'sku' => 'NTX-HONG-1KG'],
            ['shop' => 0, 'category' => 'trai-cay', 'name' => 'Bơ Sáp 034 Lâm Đồng', 'origin' => 'Bảo Lộc, Lâm Đồng', 'price' => 79000, 'stock' => 55, 'unit' => 'kg', 'variant' => 'Túi 1kg', 'sku' => 'NTX-BO-1KG'],
            ['shop' => 0, 'category' => 'rau-cu', 'name' => 'Rau Cải Ngọt Đà Lạt', 'origin' => 'Đà Lạt, Lâm Đồng', 'price' => 18000, 'stock' => 120, 'unit' => 'bó', 'variant' => 'Bó 500g', 'sku' => 'NTX-CAI-500G'],
            ['shop' => 0, 'category' => 'rau-cu', 'name' => 'Cà Rốt Đà Lạt', 'origin' => 'Đơn Dương, Lâm Đồng', 'price' => 29000, 'stock' => 95, 'unit' => 'kg', 'variant' => 'Túi 1kg', 'sku' => 'NTX-CAROT-1KG'],
            ['shop' => 0, 'category' => 'rau-cu', 'name' => 'Khoai Tây Đà Lạt', 'origin' => 'Đà Lạt, Lâm Đồng', 'price' => 36000, 'stock' => 80, 'unit' => 'kg', 'variant' => 'Túi 1kg', 'sku' => 'NTX-KHOAITAY-1KG'],
            ['shop' => 0, 'category' => 'rau-cu', 'name' => 'Bắp Cải Tím Đà Lạt', 'origin' => 'Đà Lạt, Lâm Đồng', 'price' => 32000, 'stock' => 65, 'unit' => 'kg', 'variant' => 'Bắp 1kg', 'sku' => 'NTX-BAPCAI-1KG'],
            ['shop' => 0, 'category' => 'rau-cu', 'name' => 'Súp Lơ Xanh Hữu Cơ', 'origin' => 'Đức Trọng, Lâm Đồng', 'price' => 49000, 'stock' => 50, 'unit' => 'kg', 'variant' => 'Gói 1kg', 'sku' => 'NTX-SUPLO-1KG'],
            ['shop' => 0, 'category' => 'rau-cu', 'name' => 'Cà Chua Beef Đà Lạt', 'origin' => 'Đà Lạt, Lâm Đồng', 'price' => 42000, 'stock' => 88, 'unit' => 'kg', 'variant' => 'Hộp 1kg', 'sku' => 'NTX-CACHUA-1KG'],
            ['shop' => 0, 'category' => 'nam', 'name' => 'Nấm Hương Tươi Đà Lạt', 'origin' => 'Đà Lạt, Lâm Đồng', 'price' => 85000, 'stock' => 40, 'unit' => 'g', 'variant' => 'Hộp 500g', 'sku' => 'NTX-NAMHUONG-500G'],
            ['shop' => 0, 'category' => 'nam', 'name' => 'Nấm Đùi Gà Tươi', 'origin' => 'Đà Lạt, Lâm Đồng', 'price' => 72000, 'stock' => 48, 'unit' => 'g', 'variant' => 'Hộp 500g', 'sku' => 'NTX-NAMDUI-500G'],
            ['shop' => 0, 'category' => 'ca-phe-va-tra', 'name' => 'Cà Phê Arabica Cầu Đất', 'origin' => 'Cầu Đất, Lâm Đồng', 'price' => 185000, 'stock' => 35, 'unit' => 'g', 'variant' => 'Gói 500g', 'sku' => 'NTX-ARABICA-500G'],
            ['shop' => 0, 'category' => 'ca-phe-va-tra', 'name' => 'Trà Oolong Lâm Đồng', 'origin' => 'Bảo Lộc, Lâm Đồng', 'price' => 165000, 'stock' => 42, 'unit' => 'g', 'variant' => 'Hộp 250g', 'sku' => 'NTX-OOLONG-250G'],
            ['shop' => 0, 'category' => 'do-uong', 'name' => 'Nước Atiso Đà Lạt', 'origin' => 'Đà Lạt, Lâm Đồng', 'price' => 48000, 'stock' => 75, 'unit' => 'chai', 'variant' => 'Chai 500ml', 'sku' => 'NTX-ATISO-500ML'],
            ['shop' => 0, 'category' => 'san-pham-huu-co', 'name' => 'Combo Rau Củ Hữu Cơ Đà Lạt', 'origin' => 'Đà Lạt, Lâm Đồng', 'price' => 159000, 'stock' => 30, 'unit' => 'combo', 'variant' => 'Combo 5kg', 'sku' => 'NTX-COMBO-5KG'],

            // Đặc Sản Việt
            ['shop' => 1, 'category' => 'gao-va-ngu-coc', 'name' => 'Gạo ST25 Sóc Trăng', 'origin' => 'Sóc Trăng', 'price' => 195000, 'stock' => 75, 'unit' => 'túi', 'variant' => 'Túi 5kg', 'sku' => 'DSV-ST25-5KG'],
            ['shop' => 1, 'category' => 'gao-va-ngu-coc', 'name' => 'Gạo Tám Xoan Hải Hậu', 'origin' => 'Hải Hậu, Nam Định', 'price' => 175000, 'stock' => 60, 'unit' => 'túi', 'variant' => 'Túi 5kg', 'sku' => 'DSV-TAMXOAN-5KG'],
            ['shop' => 1, 'category' => 'gao-va-ngu-coc', 'name' => 'Đậu Đen Xanh Lòng', 'origin' => 'Nghệ An', 'price' => 58000, 'stock' => 90, 'unit' => 'kg', 'variant' => 'Túi 1kg', 'sku' => 'DSV-DAUDEN-1KG'],
            ['shop' => 1, 'category' => 'hat-va-qua-kho', 'name' => 'Hạt Điều Rang Muối Bình Phước', 'origin' => 'Bình Phước', 'price' => 225000, 'stock' => 55, 'unit' => 'g', 'variant' => 'Hũ 500g', 'sku' => 'DSV-HATDIEU-500G'],
            ['shop' => 1, 'category' => 'hat-va-qua-kho', 'name' => 'Hạt Macca Tây Nguyên', 'origin' => 'Đắk Lắk', 'price' => 245000, 'stock' => 40, 'unit' => 'g', 'variant' => 'Hũ 500g', 'sku' => 'DSV-MACCA-500G'],
            ['shop' => 1, 'category' => 'mat-ong', 'name' => 'Mật Ong Hoa Cà Phê', 'origin' => 'Buôn Ma Thuột, Đắk Lắk', 'price' => 139000, 'stock' => 58, 'unit' => 'chai', 'variant' => 'Chai 500ml', 'sku' => 'DSV-MATONG-500ML'],
            ['shop' => 1, 'category' => 'gia-vi-va-dac-san', 'name' => 'Tiêu Đen Phú Quốc', 'origin' => 'Phú Quốc, Kiên Giang', 'price' => 89000, 'stock' => 70, 'unit' => 'g', 'variant' => 'Hũ 250g', 'sku' => 'DSV-TIEUDEN-250G'],
            ['shop' => 1, 'category' => 'gia-vi-va-dac-san', 'name' => 'Nước Mắm Nhĩ Phú Quốc', 'origin' => 'Phú Quốc, Kiên Giang', 'price' => 125000, 'stock' => 65, 'unit' => 'chai', 'variant' => 'Chai 500ml', 'sku' => 'DSV-NUOCMAM-500ML'],
            ['shop' => 1, 'category' => 'thuc-pham-kho', 'name' => 'Miến Dong Cao Bằng', 'origin' => 'Nguyên Bình, Cao Bằng', 'price' => 68000, 'stock' => 80, 'unit' => 'gói', 'variant' => 'Gói 500g', 'sku' => 'DSV-MIENDONG-500G'],
            ['shop' => 1, 'category' => 'thuc-pham-kho', 'name' => 'Măng Khô Tây Bắc', 'origin' => 'Sơn La', 'price' => 155000, 'stock' => 38, 'unit' => 'g', 'variant' => 'Túi 500g', 'sku' => 'DSV-MANGKHO-500G'],
            ['shop' => 1, 'category' => 'nam', 'name' => 'Nấm Hương Khô Tây Bắc', 'origin' => 'Lào Cai', 'price' => 115000, 'stock' => 44, 'unit' => 'g', 'variant' => 'Túi 300g', 'sku' => 'DSV-NAMKHO-300G'],
            ['shop' => 1, 'category' => 'banh-va-dac-san', 'name' => 'Bánh Đậu Xanh Hải Dương', 'origin' => 'Hải Dương', 'price' => 75000, 'stock' => 85, 'unit' => 'hộp', 'variant' => 'Hộp 400g', 'sku' => 'DSV-BANHDAU-400G'],
            ['shop' => 1, 'category' => 'banh-va-dac-san', 'name' => 'Kẹo Dừa Bến Tre', 'origin' => 'Bến Tre', 'price' => 62000, 'stock' => 92, 'unit' => 'gói', 'variant' => 'Gói 500g', 'sku' => 'DSV-KEODUA-500G'],
            ['shop' => 1, 'category' => 'thit-va-san-pham-che-bien', 'name' => 'Trâu Gác Bếp Tây Bắc', 'origin' => 'Điện Biên', 'price' => 465000, 'stock' => 25, 'unit' => 'g', 'variant' => 'Túi 500g', 'sku' => 'DSV-TRAU-500G'],
            ['shop' => 1, 'category' => 'qua-bieu-va-combo', 'name' => 'Hộp Quà Đặc Sản Việt', 'origin' => 'Việt Nam', 'price' => 599000, 'stock' => 20, 'unit' => 'hộp', 'variant' => 'Hộp quà tiêu chuẩn', 'sku' => 'DSV-QUA-01'],

            // Nông Sản Miền Tây
            ['shop' => 2, 'category' => 'trai-cay', 'name' => 'Xoài Cát Hòa Lộc', 'origin' => 'Cái Bè, Tiền Giang', 'price' => 85000, 'stock' => 75, 'unit' => 'kg', 'variant' => 'Túi 1kg', 'sku' => 'NSMT-XOAI-1KG'],
            ['shop' => 2, 'category' => 'trai-cay', 'name' => 'Bưởi Da Xanh Bến Tre', 'origin' => 'Châu Thành, Bến Tre', 'price' => 72000, 'stock' => 68, 'unit' => 'quả', 'variant' => 'Quả 1.2kg', 'sku' => 'NSMT-BUOI-1Q'],
            ['shop' => 2, 'category' => 'trai-cay', 'name' => 'Sầu Riêng Ri6', 'origin' => 'Cái Mơn, Bến Tre', 'price' => 165000, 'stock' => 45, 'unit' => 'kg', 'variant' => 'Trái 2kg', 'sku' => 'NSMT-SAURIENG-2KG'],
            ['shop' => 2, 'category' => 'trai-cay', 'name' => 'Dừa Xiêm Xanh Bến Tre', 'origin' => 'Bến Tre', 'price' => 22000, 'stock' => 150, 'unit' => 'trái', 'variant' => 'Trái tươi', 'sku' => 'NSMT-DUA-1TRAI'],
            ['shop' => 2, 'category' => 'trai-cay', 'name' => 'Thanh Long Ruột Đỏ', 'origin' => 'Hàm Thuận Nam, Bình Thuận', 'price' => 42000, 'stock' => 95, 'unit' => 'kg', 'variant' => 'Túi 1kg', 'sku' => 'NSMT-THANHLONG-1KG'],
            ['shop' => 2, 'category' => 'trai-cay', 'name' => 'Chôm Chôm Nhãn', 'origin' => 'Vĩnh Long', 'price' => 48000, 'stock' => 88, 'unit' => 'kg', 'variant' => 'Túi 1kg', 'sku' => 'NSMT-CHOMCHOM-1KG'],
            ['shop' => 2, 'category' => 'trai-cay', 'name' => 'Nhãn Xuồng Cơm Vàng', 'origin' => 'Bà Rịa - Vũng Tàu', 'price' => 69000, 'stock' => 62, 'unit' => 'kg', 'variant' => 'Túi 1kg', 'sku' => 'NSMT-NHAN-1KG'],
            ['shop' => 2, 'category' => 'rau-cu', 'name' => 'Khoai Lang Mật Vĩnh Long', 'origin' => 'Bình Tân, Vĩnh Long', 'price' => 34000, 'stock' => 105, 'unit' => 'kg', 'variant' => 'Túi 1kg', 'sku' => 'NSMT-KHOAILANG-1KG'],
            ['shop' => 2, 'category' => 'rau-cu', 'name' => 'Bí Đỏ Hồ Lô', 'origin' => 'Cần Thơ', 'price' => 27000, 'stock' => 82, 'unit' => 'kg', 'variant' => 'Quả khoảng 1kg', 'sku' => 'NSMT-BIDO-1KG'],
            ['shop' => 2, 'category' => 'thuy-hai-san', 'name' => 'Tôm Khô Cà Mau', 'origin' => 'Cà Mau', 'price' => 285000, 'stock' => 35, 'unit' => 'g', 'variant' => 'Hũ 500g', 'sku' => 'NSMT-TOMKHO-500G'],
            ['shop' => 2, 'category' => 'thuy-hai-san', 'name' => 'Cá Lóc Khô Đồng Tháp', 'origin' => 'Tam Nông, Đồng Tháp', 'price' => 235000, 'stock' => 32, 'unit' => 'g', 'variant' => 'Túi 500g', 'sku' => 'NSMT-CALOC-500G'],
            ['shop' => 2, 'category' => 'gia-vi-va-dac-san', 'name' => 'Mắm Cá Linh Châu Đốc', 'origin' => 'Châu Đốc, An Giang', 'price' => 98000, 'stock' => 55, 'unit' => 'hũ', 'variant' => 'Hũ 500g', 'sku' => 'NSMT-MAMCALINH-500G'],
            ['shop' => 2, 'category' => 'banh-va-dac-san', 'name' => 'Bánh Pía Sóc Trăng', 'origin' => 'Sóc Trăng', 'price' => 92000, 'stock' => 76, 'unit' => 'hộp', 'variant' => 'Hộp 4 bánh', 'sku' => 'NSMT-BANHPIA-4'],
            ['shop' => 2, 'category' => 'do-uong', 'name' => 'Nước Dừa Tươi Đóng Chai', 'origin' => 'Bến Tre', 'price' => 35000, 'stock' => 110, 'unit' => 'chai', 'variant' => 'Chai 500ml', 'sku' => 'NSMT-NUOCDUA-500ML'],
            ['shop' => 2, 'category' => 'qua-bieu-va-combo', 'name' => 'Giỏ Quà Trái Cây Miền Tây', 'origin' => 'Miền Tây Nam Bộ', 'price' => 449000, 'stock' => 24, 'unit' => 'combo', 'variant' => 'Giỏ quà 5 loại trái cây', 'sku' => 'NSMT-GIOQUA-01'],
        ];

        foreach ($products as $item) {
            $category = $categories->get($item['category']);
            $unit = $units->get($item['unit']);
            $seller = $shops->get($shopEmails[$item['shop']]);

            if (!$category) {
                throw new RuntimeException(
                    "Không tìm thấy danh mục: {$item['category']}"
                );
            }

            if (!$unit) {
                throw new RuntimeException(
                    "Không tìm thấy đơn vị: {$item['unit']}"
                );
            }

            $description = $item['name']
                .' được tuyển chọn tại '.$item['origin']
                .', bảo đảm nguồn gốc rõ ràng, chất lượng ổn định '
                .'và phù hợp cho nhu cầu sử dụng hằng ngày.';

            $product = Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'seller_id' => $seller->id,
                    'category_id' => $category->id,
                    'description' => $description,
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'origin' => $item['origin'],
                    'status' => true,
                ]
            );

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'search_name' => Product::normalizeSearchText(
                        $product->name
                    ),
                    'search_description' => Product::normalizeSearchText(
                        $product->description ?? ''
                    ),
                ]);
            $product->variants()->updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'name' => $item['variant'],
                    'unit_id' => $unit->id,
                    'quantity' => 1,
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'status' => true,
                ]
            );
        }
    }
}
