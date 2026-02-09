<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->invoice_number }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }

        .invoice-container {
            width: 100%;
            max-width: 100%;
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-row {
            width: 100%;
            margin-bottom: 5px;
        }

        .info-label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }

        .info-value {
            display: inline-block;
        }

        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
            table-layout: fixed;
            /* PENTING! */
        }

        /* Nama Produk */

        table th:nth-child(3),
        table td:nth-child(3) {
            text-align: center;
        }

        /* Qty */

        table th:nth-child(4),
        table td:nth-child(4) {
            text-align: right;
        }

        /* Harga */

        table th:nth-child(5),
        table td:nth-child(5) {
            text-align: right;
        }

        /* Subtotal */

        th {
            background-color: #f0f0f0;
            padding: 8px 5px;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            word-wrap: break-word;
            overflow-wrap: break-word;
            font-size: 10px;
        }

        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
            font-size: 12px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        /* Prevent page break */
        tr {
            page-break-inside: avoid;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    {{-- {{ dd($order) }} --}}
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <p>amazingshoeID.</p>
            <p style="font-size: 9px;">Alamat Perusahaan | Telp: 021-xxx | Email: info@perusahaan.com</p>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">No. Invoice:</span>
                <span class="info-value">{{ $order->booking_trx_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal:</span>
                <span class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span class="info-value">{{ $order->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">{{ $order->is_paid ? 'LUNAS' : 'BELUM LUNAS' }}</span>
            </div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @if ($order->product)
                    <tr>
                        <td class="text-center">1</td>
                        <td>{{ $order->product->name }}</td>
                        <td class="text-center">{{ $order->qty ?? 1 }}</td>
                        <td class="text-right">Rp {{ number_format($order->sub_total_amount ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($order->grand_total_amount ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada produk</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right">TOTAL:</td>
                    <td class="text-right">Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>Terima kasih atas kepercayaan Anda</p>
            <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>

</html>
