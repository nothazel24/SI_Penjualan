<?php

use App\Models\ProductTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// catch data dari url /invoice/1(or smth..)
Route::get('/invoice/{transaction}', function ($transaction) {
    $invoice = ProductTransaction::findOrFail($transaction); // get invoice data by id

    // testing modeeee
    return Pdf::loadView('pdf.invoice', compact('invoice'))
        ->stream("invoice-{$invoice->booking_trx_id}.pdf");

    // downloadable
    // return Pdf::loadView('pdf.invoice', [
    //     'invoice' => $invoice // loadview n sending data 'invoice' to blade file (pdf/invoice.blade.php)
    // ])->download("invoice-{$invoice->booking_trx_id}.pdf");
})->name('invoice.download'); // route name
