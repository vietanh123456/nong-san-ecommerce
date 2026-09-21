<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Tìm kiếm không phân biệt chữ hoa, chữ thường & dấu tiếng Việt
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('description', 'LIKE', '%' . $search . '%');
            });
        }

        // Lọc theo danh mục
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Sắp xếp
        if ($request->sort == 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($request->sort == 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        return view('products.index', compact('products', 'categories'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function sellerIndex()
    {
        $products = Product::where('seller_id', Auth::id())->latest()->get();
        return view('seller.products.index', compact('products'));
    }

    public function sellerCreate()
    {
        $categories = Category::orderBy('id')->get();
        return view('seller.products.form', compact('categories'));
    }

    public function sellerStore(Request $request)
    {
        $data = $this->validatedData($request);
        $data['seller_id'] = Auth::id();
        Product::create($data);

        return redirect()->route('seller.products.index')->with('success', 'Đã thêm sản phẩm.');
    }

    public function sellerEdit(Product $product)
    {
        $this->ensureOwner($product);
        $categories = Category::orderBy('id')->get();
        return view('seller.products.form', compact('product', 'categories'));
    }

    public function sellerUpdate(Request $request, Product $product)
    {
        $this->ensureOwner($product);
        $product->update($this->validatedData($request));

        return redirect()->route('seller.products.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function sellerDestroy(Product $product)
    {
        $this->ensureOwner($product);
        $product->delete();

        return back()->with('success', 'Đã xóa sản phẩm.');
    }

    public function sellerToggle(Product $product)
    {
        $this->ensureOwner($product);
        $product->update(['status' => ! $product->status]);

        return back()->with('success', 'Đã cập nhật trạng thái sản phẩm.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'origin' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'url', 'max:2048'],
            'status' => ['nullable', 'boolean'],
        ]);
    }

    private function ensureOwner(Product $product): void
    {
        abort_unless($product->seller_id === Auth::id(), 403);
    }
}