<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateFileController extends Controller
{
    public function public(Certificate $certificate): StreamedResponse
    {
        abort_unless($certificate->status === Certificate::STATUS_APPROVED, 404);

        return $this->respond($certificate);
    }

    public function seller(Request $request, Certificate $certificate): StreamedResponse
    {
        abort_unless(
            $certificate->batch()->whereHas(
                'product',
                fn ($query) => $query->where('seller_id', $request->user()->id)
            )->exists(),
            403
        );

        return $this->respond($certificate);
    }

    public function admin(Certificate $certificate): StreamedResponse
    {
        return $this->respond($certificate);
    }

    private function respond(Certificate $certificate): StreamedResponse
    {
        $disk = Storage::disk('local')->exists($certificate->file_path)
            ? 'local'
            : 'public';

        abort_unless(Storage::disk($disk)->exists($certificate->file_path), 404);

        return Storage::disk($disk)->response(
            $certificate->file_path,
            $certificate->name
        );
    }
}
