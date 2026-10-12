<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - {{ $transaction->invoice_number }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 48mm; /* Area cetak bersih untuk printer 58mm */
            margin: 0 auto;
            padding: 8px 0;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .line {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
            vertical-align: top;
        }
        .bold { font-weight: bold; }
    </style>
</head>
<body onload="window.print(); window.onafterprint = function(){ window.close(); }">

    <!-- Header Toko -->
    <div class="text-center">
        <h3 style="margin: 0; font-size: 14px;" class="bold">UNIVERSITAS NEGERI PADANG</h3>
        <p style="margin: 2px 0; font-size: 10px;">Jalan Prof. Dr. Hamka, Air Tawar, Kota Padang, Sumatera Barat, Indonesia</p>
        <p style="margin: 0; font-size: 10px;">Telp: 0838-3682-7148</p>
    </div>

    <div class="line"></div>

    <!-- Informasi Transaksi -->
    <div>
        <table>
            <tr>
                <td>No. Transaksi</td>
                <td>:</td>
                <td class="text-right bold">{{ $transaction->invoice_number }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td class="text-right">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>:</td>
                <td class="text-right">{{ $transaction->user->name ?? 'Kasir' }}</td>
            </tr>
        </table>
    </div>

    <div class="line"></div>

    <!-- Rincian Item Belanja -->
    <table>
        @foreach ($transaction->transaction_detail as $detail)
            <tr>
                <td colspan="2" class="bold">{{ $detail->product->name }}</td>
            </tr>
            <tr>
                <td>{{ $detail->qty }} x {{ number_format($detail->price, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <!-- Rincian Pembayaran -->
    <table>
        @if($transaction->has_ppn)
        <tr>
            <td>PPN</td>
            <td class="text-right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="bold">
            <td>Total:</td>
            <td class="text-right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bayar:</td>
            <td class="text-right">Rp {{ number_format($transaction->pay_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali:</td>
            <td class="text-right">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <!-- Footer Struk -->
    <div class="text-center" style="margin-top: 10px;">
        <p style="margin: 0;" class="bold">-- TERIMA KASIH --</p>
        <p style="margin: 2px 0; font-size: 9px;">Barang yang sudah dibeli</p>
        <p style="margin: 0; font-size: 9px;">tidak dapat ditukar/dikembalikan</p>
    </div>

</body>
</html>
