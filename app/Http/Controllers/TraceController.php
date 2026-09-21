<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\ProductBatch;
use Illuminate\View\View;

class TraceController extends Controller
{
    public function show(string $batchCode): View
    {
        $batch = ProductBatch::query()->with([
            'product.category',
            'certificates' => fn ($query) => $query->where('status', Certificate::STATUS_APPROVED),
        ])->where('batch_code', $batchCode)->firstOrFail();

        return view('trace.show', compact('batch'));
    }
}
