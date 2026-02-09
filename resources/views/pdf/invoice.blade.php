@extends('pdf.layouts.invoice')

{{-- catch & re-define data 'invoice' to $invoice --}}
@section('content')
    <h2 class="text-center font-bold mb-6">
        INVOICE
    </h2>

    <div class="mb-4">
        <strong>Nama Customer:</strong> {{ $invoice->name }}<br>
        <strong>No Booking:</strong> {{ $invoice->booking_trx_id }}<br>
        <strong>Tanggal Pemesanan:</strong>
        {{ $invoice->created_at->format('d M Y') }}
    </div>

    <table class="mb-4">
        <thead>
            <tr>
                <th style="width: 40px">No</th>
                <th>Produk</th>
                <th style="width: 60px">Qty</th>
                <th style="width: 120px">Sub Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>{{ $invoice->product->name }}</td> {{-- RELATION BTW: ambil data nama dari product --}}
                <td class="text-center">{{ $invoice->qty }}</td>
                <td class="text-right">
                    Rp {{ number_format($invoice->sub_total_amount, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="text-right font-bold">
        Total: Rp {{ number_format($invoice->grand_total_amount, 0, ',', '.') }}
    </div>
@endsection
