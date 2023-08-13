<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    //show qr code
    public function show(string $value)
    {
        $output = QrCode::size(100)->generate(route('orders.receipt', route('orders.show', $value)));
        $response = Response::make(View::make('qrcode.show', [
            'output' => $output
        ]), 200);
        $response->header('Content-Type', 'image/svg+xml');
        return $response;
    }
}
