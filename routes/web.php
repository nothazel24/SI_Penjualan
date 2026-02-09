<?php

use App\Models\ProductTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invoice/{transaction}', function ($transaction) {
    $invoice = ProductTransaction::findOrFail($transaction);

    return Pdf::loadView('pdf.invoice', [
        'invoice' => $invoice
    ])->download("invoice-{$invoice->booking_trx_id}.pdf");
})->name('invoice.download');
