<?php

use App\Models\ProductTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;

Route::get('/', function () {
    return view('welcome');
});

// catch data dari url /invoice/1(or smth..)
Route::get('/invoice/{transaction}', function ($transaction) {
    $invoice = ProductTransaction::findOrFail($transaction); // get invoice data by id

    // testing modeeee
    // coba ke ubah cara pengiriman datana jadi [invoice => $invoice] (!1)
    return Pdf::loadView('pdf.invoice', compact('invoice'))
        ->stream("Invoice-{$invoice->booking_trx_id}.pdf");

    // downloadable
    // return Pdf::loadView('pdf.invoice', [
    //     'invoice' => $invoice // loadview n sending data 'invoice' to blade file (pdf/invoice.blade.php)
    // ])->download("invoice-{$invoice->booking_trx_id}.pdf");
})->name('invoice.download'); // route name

Route::get('/reports/transaction/pdf', function () {
    // get data by http request url
    $start = request('start');
    $end = request('end');

    // konek dengan model dan masukkan data yang dicari kedalam variabel $transaction
    $transaction = ProductTransaction::with('product') // ('product') itu relasina btw.
        ->whereBetween('created_at', [
            // parsing data
            Carbon::parse($start)->startOfDay(),
            Carbon::parse($end)->endOfDay()
        ])
        ->get();

    // coba ke ubah pengiiriman datana jadi compact() (!2)
    return Pdf::loadView('pdf.transaction-reports', [
        'transactions' => $transaction,
        'startDate' => $start,
        'endDate' => $end
    ])->stream('Generate-laporan.pdf');
})->name('report.transactions.pdf');
