<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $statistics = [
            'users' => User::count(), 'sellers' => User::where('role', 'seller')->count(),
            'products' => Product::count(),
            'active_products' => Product::where('status', true)->count(),
            'pending_certificates' => Certificate::where('status', Certificate::STATUS_PENDING)->count(),
            'pending_reviews' => Review::where('status', Review::STATUS_PENDING)->count(),
        ];
        $certificateStatus = [
            'approved' => Certificate::where('status', Certificate::STATUS_APPROVED)->count(),
            'pending' => $statistics['pending_certificates'],
            'rejected' => Certificate::where('status', Certificate::STATUS_REJECTED)->count(),
        ];
        $certificates = Certificate::with('batch.product')
            ->where('status', Certificate::STATUS_PENDING)
            ->latest()
            ->limit(5)
            ->get();
        $recentProducts = Product::with(['seller', 'category'])
            ->latest()
            ->limit(5)
            ->get();
        $recentUsers = User::latest()->limit(5)->get();
        $recentReviews = Review::with(['product', 'user'])
            ->latest()
            ->limit(4)
            ->get();

        return view('admin.dashboard', compact(
            'statistics',
            'certificateStatus',
            'certificates',
            'recentProducts',
            'recentUsers',
            'recentReviews'
        ));
    }

    public function users(): View
    {
        $filters = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(['customer', 'seller', 'admin'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $users = User::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', compact('users', 'filters'));
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', Rule::in(['customer', 'seller', 'admin'])]]);

        if ($user->is(auth()->user()) && $data['role'] !== 'admin') {
            return back()->with('error', 'Bạn không thể tự hạ quyền quản trị của mình.');
        }

        if ($user->role === 'admin' && $data['role'] !== 'admin' && $this->isLastActiveAdmin($user)) {
            return back()->with('error', 'Không thể hạ quyền quản trị viên cuối cùng.');
        }

        $user->update($data);

        return back()->with('success', 'Đã cập nhật vai trò tài khoản.');
    }

    public function toggleUserStatus(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của mình.');
        }

        if ($user->is_active && $user->role === 'admin' && $this->isLastActiveAdmin($user)) {
            return back()->with('error', 'Không thể khóa quản trị viên cuối cùng.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.');
    }

    public function products(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'hidden'])],
        ]);
        $products = Product::query()
            ->with(['seller', 'category'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('origin', 'like', "%{$search}%")
                        ->orWhereHas('seller', fn ($sellerQuery) => $sellerQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status === 'active'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products', compact('products', 'filters'));
    }

    public function toggleProduct(Product $product): RedirectResponse
    {
        $product->update(['status' => ! $product->status]);

        return back()->with('success', 'Đã cập nhật trạng thái sản phẩm.');
    }

    public function certificates(): View
    {
        $certificates = Certificate::with(['batch.product', 'approver'])->latest()->paginate(20);

        return view('admin.certificates', compact('certificates'));
    }

    public function reviewCertificate(Request $request, Certificate $certificate): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([Certificate::STATUS_APPROVED, Certificate::STATUS_REJECTED])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $certificate->update($data + ['approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', 'Đã xử lý chứng nhận.');
    }

    public function reviews(): View
    {
        $reviews = Review::with(['product', 'user'])->latest()->paginate(20);

        return view('admin.reviews', compact('reviews'));
    }

    public function reviewReview(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in([Review::STATUS_APPROVED, Review::STATUS_REJECTED])]]);
        $review->update($data + ['moderated_by' => auth()->id(), 'moderated_at' => now()]);

        return back()->with('success', 'Đã cập nhật trạng thái đánh giá.');
    }

    private function isLastActiveAdmin(User $user): bool
    {
        return User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->doesntExist();
    }
}
