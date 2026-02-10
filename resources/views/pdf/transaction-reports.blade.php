<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
</head>

<body>

    <h2>Laporan Transaksi</h2>
    <p>
        Periode:
        {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
        –
        {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
    </p>

    <table width="100%" border="1" cellspacing="0" cellpadding="6">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $i => $trx)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $trx->created_at->format('d-m-Y') }}</td>
                    <td>{{ $trx->product->name }}</td>
                    <td>{{ $trx->qty }}</td>
                    <td>
                        Rp {{ number_format($trx->grand_total_amount, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
