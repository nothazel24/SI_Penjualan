<?php

use Illuminate\Support\Facades\Route;
use App\Service\TransactionReportService; // backend logic
use Barryvdh\DomPDF\Facade\Pdf; // pdf library
use Illuminate\Support\Carbon;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reports/transactions', function (TransactionReportService $service) {
    $data = $service->getData(request()->all());

    return Pdf::loadView('reports.transactions', $data)
        ->download('laporan-transaksi.pdf');
})->name('reports.transactions');
