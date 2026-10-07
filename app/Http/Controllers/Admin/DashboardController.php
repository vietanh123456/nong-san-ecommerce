<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SellerRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $statistics = [
            'users' => User::count(),
            'buyers' => User::where('role', 'buyer')->count(),
            'sellers' => User::where('role', 'seller')->count(),
            'pending_seller_requests' => SellerRequest::where('status', 'pending')->count(),
            'products' => Product::count(),
            'categories' => Category::count(),
        ];

        return view('admin.dashboard', compact('statistics'));
    }
}
