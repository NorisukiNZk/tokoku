<?php

namespace App\Http\Controllers;

use App\Models\ServiceTicket;
use App\Models\StoreSetting;
use App\Services\QrCodeSvgService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffServiceStickerController extends Controller
{
    public function show(Request $request, ServiceTicket $ticket, QrCodeSvgService $qr)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role, ['admin', 'technician'], true)) {
            abort(403, 'Hanya staf toko atau teknisi yang dapat mencetak stiker label bengkel.');
        }

        $store = StoreSetting::current();
        $trackingUrl = route('service.index', [
            'tiket' => $ticket->ticket_number,
            'pin' => $ticket->phoneLastFour(),
        ]);

        $widthMm = $request->query('lebar') === '80' ? 80 : 58;
        $qrSvg = $qr->generateInlineSvg($trackingUrl, $widthMm === 80 ? 116 : 96, 1);

        return view('print.service-sticker', [
            'store' => $store,
            'ticket' => $ticket->loadMissing('servicePackage'),
            'trackingUrl' => $trackingUrl,
            'qrSvg' => $qrSvg,
            'widthMm' => $widthMm,
        ]);
    }
}
