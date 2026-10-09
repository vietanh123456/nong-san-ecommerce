<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SellerRequestController extends Controller
{
    public function index(): View
    {
        $requests = SellerRequest::query()
            ->with('user')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->paginate(15);

        return view('admin.seller-requests', compact('requests'));
    }

    public function approve(SellerRequest $sellerRequest): RedirectResponse
    {
        $approved = DB::transaction(function () use ($sellerRequest): bool {
            $lockedRequest = SellerRequest::query()
                ->whereKey($sellerRequest->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedRequest || $lockedRequest->status !== 'pending') {
                return false;
            }

            $user = User::query()
                ->whereKey($lockedRequest->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($user->role !== 'buyer') {
                return false;
            }

            $lockedRequest->update(['status' => 'approved']);
            $user->update(['role' => 'seller']);

            return true;
        });

        if (! $approved) {
            return $this->invalidRequestResponse();
        }

        return redirect()
            ->route('admin.seller-requests.index')
            ->with('success', "Đã duyệt yêu cầu của {$sellerRequest->user->name}.");
    }

    public function reject(SellerRequest $sellerRequest): RedirectResponse
    {
        $rejected = SellerRequest::query()
            ->whereKey($sellerRequest->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        if (! $rejected) {
            return $this->invalidRequestResponse();
        }

        return redirect()
            ->route('admin.seller-requests.index')
            ->with('success', "Đã từ chối yêu cầu của {$sellerRequest->user->name}.");
    }

    private function invalidRequestResponse(): RedirectResponse
    {
        return redirect()
            ->route('admin.seller-requests.index')
            ->with('warning', 'Yêu cầu không tồn tại hoặc đã được xử lý.');
    }
}
