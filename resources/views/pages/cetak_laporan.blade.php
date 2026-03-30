<!DOCTYPE html>
<html>

<head>
    <title>Transaction Report - Toko Plastik Asa Baru</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            font-size: 12px;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
        }

        .header td {
            border: none;
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f2f2f2;
            border-bottom: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        td {
            padding: 8px;
        }

        .table-items td {
            border-bottom: 1px solid #ddd;
        }

        .total {
            margin-top: 20px;
            width: 40%;
            float: right;
        }

        .total td {
            padding: 6px;
        }

        .footer {
            margin-top: 80px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            font-size: 11px;
        }

        .logo-image {
            height: 64px;
            object-fit: fill;
            display: block;
        }
    </style>

</head>

<body>
    <table class="header">
        <tr>
            <td>
                <img src="{{ public_path('images/medium_logo.png') }}" alt="logo-image" class="logo-image">
                <br>Jl. Jamin Ginting No.328, Padang Bulan, Medan Baru,<br>
                Kota Medan, Sumatera Utara 20155
            </td>

            <td style="text-align:right">
                <b>Tanggal:</b> {{ date('d/m/Y') }} <br>
                <b>Report #:</b> RPT-{{ rand(100, 999) }}
            </td>
        </tr>
    </table>

    <h1 class="title">TRANSACTION REPORT</h1>

    <table class="table-items">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Harga/Satuan</th>
                <th>Stok</th>
                <th>Tipe Transaksi</th>
                <th>Sub Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($stockInAndOuts as $stockInAndOut)
                @php
                    if ($stockInAndOut['transaction']['transaction_type'] == 'masuk') {
                        $sub = $stockInAndOut['amount'] * $stockInAndOut['capital_price'];
                    } else {
                        $sub = $stockInAndOut['amount'] * $stockInAndOut['selling_price'];
                    }
                @endphp

                <tr>
                    <td>{{ $stockInAndOut['item_name'] }}</td>
                    <td>
                        Rp
                        {{ number_format(
                            $stockInAndOut['transaction']['transaction_type'] == 'masuk'
                                ? $stockInAndOut['capital_price']
                                : $stockInAndOut['selling_price'],
                            0,
                            ',',
                            '.',
                        ) }}
                    </td>
                    <td>{{ $stockInAndOut['amount'] }}</td>
                    <td>{{ ucwords($stockInAndOut['transaction']['transaction_type']) }}</td>
                    <td>Rp {{ number_format($sub, 0, ',', '.') }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>

    <table class="total">
        <tr>
            <td><b>Total Pembelian</b></td>
            <td>Rp {{ number_format($stockIn, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td><b>Total Penjualan</b></td>
            <td>Rp {{ number_format($stockOut, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td><b>Keuntungan</b></td>
            <td>
                <b>Rp {{ number_format($profit, 0, ',', '.') }}</b>
            </td>
        </tr>
    </table>

    <div style="clear:both"></div>

    <div class="footer">
        <b>Toko Plastik Asa Baru</b><br>
        Jl. Jamin Ginting No.328, Padang Bulan, Medan Baru,<br>
        Kota Medan, Sumatera Utara 20155<br>
        Telp: +62 812 6445 1073
    </div>

</body>

</html>
