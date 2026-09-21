<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Product;
use App\Models\ProductBatch;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductBatchController extends Controller
{
    public function index(): View
    {
        $batches = ProductBatch::query()->with('product')
            ->whereHas('product', fn ($query) => $query->where('seller_id', auth()->id()))
            ->latest()->paginate(12);

        return view('seller.batches.index', compact('batches'));
    }

    public function create(): View
    {
        $products = Product::query()->where('seller_id', auth()->id())
            ->orderBy('name')->get();

        return view('seller.batches.form', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $batch = ProductBatch::create($this->validatedData($request));
        $this->generateQr($batch);

        return redirect()->route('seller.batches.show', $batch)
            ->with('success', 'Tạo lô hàng và mã QR thành công.');
    }

    public function show(ProductBatch $batch): View
    {
        $this->ensureOwned($batch);
        $batch->load(['product', 'certificates']);

        return view('seller.batches.show', compact('batch'));
    }

    public function edit(ProductBatch $batch): View
    {
        $this->ensureOwned($batch);
        $products = Product::query()->where('seller_id', auth()->id())
            ->orderBy('name')->get();

        return view('seller.batches.form', compact('batch', 'products'));
    }

    public function update(Request $request, ProductBatch $batch): RedirectResponse
    {
        $this->ensureOwned($batch);
        $batch->update($this->validatedData($request, $batch));
        $this->generateQr($batch->fresh());

        return redirect()->route('seller.batches.show', $batch)
            ->with('success', 'Cập nhật lô hàng thành công.');
    }

    public function destroy(ProductBatch $batch): RedirectResponse
    {
        $this->ensureOwned($batch);
        Storage::disk('public')->delete($batch->qr_path);
        $batch->certificates()->each(function (Certificate $certificate): void {
            Storage::disk('local')->delete($certificate->file_path);
            Storage::disk('public')->delete($certificate->file_path);
        });
        $batch->delete();

        return redirect()->route('seller.batches.index')->with('success', 'Đã xóa lô hàng.');
    }

    public function storeCertificate(Request $request, ProductBatch $batch): RedirectResponse
    {
        $this->ensureOwned($batch);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $batch->certificates()->create([
            'name' => $data['name'],
            'file_path' => $request->file('file')->store('certificates', 'local'),
        ]);

        return back()->with('success', 'Đã tải chứng nhận, chờ quản trị viên duyệt.');
    }

    private function validatedData(Request $request, ?ProductBatch $batch = null): array
    {
        $data = $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('seller_id', auth()->id()),
            ],
            'batch_code' => ['required', 'string', 'max:100', Rule::unique('product_batches')->ignore($batch)],
            'origin' => ['required', 'string', 'max:255'],
            'producer' => ['nullable', 'string', 'max:255'],
            'harvest_date' => ['required', 'date'],
            'packaged_date' => ['required', 'date', 'after_or_equal:harvest_date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:packaged_date'],
        ]);
        $data['production_date'] = $data['harvest_date'];

        return $data;
    }

    private function ensureOwned(ProductBatch $batch): void
    {
        abort_unless($batch->product()->where('seller_id', auth()->id())->exists(), 403);
    }

    private function generateQr(ProductBatch $batch): void
    {
        $url = route('trace.show', $batch->batch_code);
        $path = 'qrcodes/'.$batch->batch_code.'.svg';
        $svg = (new SvgWriter)->write(new QrCode(data: $url))->getString();
        Storage::disk('public')->put($path, $svg);
        $batch->update(['qr_code' => $url, 'qr_path' => $path]);
    }
}
