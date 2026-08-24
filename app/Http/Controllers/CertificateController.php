<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Support\Roles;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CertificateController
{
    public function download(Certificate $certificate): Response
    {
        $user = Auth::user();

        abort_unless($user !== null, 403);

        // Owner, or anyone who can reach the admin panel.
        abort_unless(
            $certificate->user_id === $user->getKey() || $user->hasAnyRole(Roles::PANEL),
            403,
        );

        $certificate->loadMissing('user');

        $meta = (array) $certificate->meta;

        if (($meta['kind'] ?? null) === 'assessment') {
            $detail = 'with a score of ' . rtrim(rtrim((string) ($meta['percentage'] ?? '0'), '0'), '.') . '%'
                . ' (' . rtrim(rtrim((string) ($meta['score'] ?? '0'), '0'), '.') . ' of ' . ($meta['total'] ?? '0') . ' points)';
        } else {
            $hours  = rtrim(rtrim((string) ($meta['hours'] ?? '0'), '0'), '.');
            $season = $meta['season'] ?? null;
            $detail = 'earning ' . $hours . ' training hour(s)' . ($season ? ' during the ' . $season . ' season' : '');
        }

        $pdf = Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
            'detail'      => $detail,
        ])->setPaper('letter', 'landscape');

        $filename = Str::slug($certificate->user->name . ' ' . $certificate->title) . '.pdf';

        return $pdf->download($filename);
    }
}
