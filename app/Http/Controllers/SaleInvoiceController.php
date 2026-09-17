<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class SaleInvoiceController extends Controller
{
    public function __invoke(Sale $sale): Response
    {
        abort_unless(auth()->user()?->hasPermission('view-sale'), 403);

        $sale->load(['customer', 'items.product', 'user']);

        $pdf = Pdf::loadView('sales.invoice', [
            'sale' => $sale,
            'company' => [
                'name' => 'Fraca Servcom Ltd',
                'address' => 'Musco Towers, Eldoret, Kenya',
                'phone' => '0725 151 495 / 0735 981 254',
                'email' => 'info@fracaservcomltd.co.ke',
            ],
        ])->setPaper('a4');

        $filename = ($sale->invoice_number ?: 'invoice-'.$sale->id).'.pdf';

        return $pdf->stream($filename);
    }
}
