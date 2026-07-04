@php
    $setting = \App\Models\Setting::first();
    $printerWidth = $setting->thermal_printer_width ?? '80mm';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order->order_number }}</title>
    <style>
        /* Optimasi Thermal Printer - Ukuran dari Settings */
        @page { margin: 0; size: {{ $printerWidth }} auto; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; margin: 0; padding: 10px; width: {{ $printerWidth }}; box-sizing: border-box; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .divider-solid { border-top: 1px solid #000; margin: 6px 0; }
        .w-full { width: 100%; }
        .mb-1 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 5px; }
        }
        
        .print-btn {
            background-color: #F97316; color: #fff; padding: 10px 20px; border: none; 
            border-radius: 5px; font-weight: bold; cursor: pointer; margin-bottom: 15px;
            font-family: Arial, sans-serif; display: block; width: 100%; text-transform: uppercase;
        }

        /* Logo */
        .logo-img { max-height: 50px; max-width: 100px; display: block; margin: 0 auto 6px; }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn no-print">🖨️ Cetak Struk</button>

    {{-- Header Struk --}}
    <div class="text-center mb-1">
        @if($setting && $setting->logo)
            <img src="{{ public_path('storage/' . $setting->logo) }}" class="logo-img" alt="Logo" onerror="this.style.display='none'">
        @endif
        <h2 style="margin: 0; font-size: {{ $printerWidth == '58mm' ? '13px' : '16px' }}; text-transform: uppercase;">{{ $setting->resto_name ?? 'RESTO KITA' }}</h2>
        @if(isset($setting->address) && $setting->address)
            <p style="margin: 2px 0; font-size: 10px;">{{ $setting->address }}</p>
        @endif
        @if(isset($setting->phone) && $setting->phone)
            <p style="margin: 2px 0; font-size: 10px;">Telp: {{ $setting->phone }}</p>
        @endif
    </div>

    <div class="divider"></div>

    {{-- Info Order --}}
    <table class="w-full">
        <tr>
            <td class="text-left">No Order</td>
            <td class="text-right font-bold">{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td class="text-left">Tanggal</td>
            <td class="text-right">{{ $order->payment->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="text-left">Meja / Tamu</td>
            <td class="text-right">{{ $order->table_number_display }} / {{ $order->customer_name ?? 'Umum' }}</td>
        </tr>
        @if($order->order_type === 'take_away')
        <tr>
            <td class="text-left font-bold" style="color: #6B21A8;">TIPE PESANAN</td>
            <td class="text-right font-bold" style="color: #6B21A8;">TAKE AWAY / BUNGKUS</td>
        </tr>
            @if($order->pickup_time)
            <tr>
                <td class="text-left">Waktu Pengambilan</td>
                <td class="text-right font-bold">{{ $order->pickup_time }}</td>
            </tr>
            @endif
            @if($order->take_away_notes)
            <tr>
                <td class="text-left">Catatan</td>
                <td class="text-right">{{ $order->take_away_notes }}</td>
            </tr>
            @endif
        @endif
        <tr>
            <td class="text-left">Kasir</td>
            <td class="text-right">{{ $order->payment->user->name ?? 'System' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- Item Pesanan --}}
    <table class="w-full mb-1">
        @foreach($order->items as $item)
        <tr>
            <td colspan="2" class="text-left font-bold">
                {{ $item->menu->name }}
                @if($item->is_take_away && $order->order_type !== 'take_away')
                    <span style="font-size: 10px; font-weight: normal;">(BUNGKUS)</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="text-left" style="padding-left: 8px;">{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</td>
            <td class="text-right font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    {{-- Subtotal & Pajak --}}
    @php
        $subtotal      = $order->items->sum('subtotal');
        $taxRate       = $setting->tax ?? 0;
        $serviceRate   = $setting->service_charge ?? 0;
        $taxAmount     = $subtotal * ($taxRate / 100);
        $serviceAmount = $subtotal * ($serviceRate / 100);
    @endphp

    <table class="w-full">
        <tr>
            <td class="text-left">Subtotal</td>
            <td class="text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($taxRate > 0)
        <tr>
            <td class="text-left">PPN ({{ $taxRate }}%)</td>
            <td class="text-right">{{ number_format($taxAmount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($serviceRate > 0)
        <tr>
            <td class="text-left">Service ({{ $serviceRate }}%)</td>
            <td class="text-right">{{ number_format($serviceAmount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td colspan="2"><div class="divider-solid"></div></td>
        </tr>
        <tr>
            <td class="text-left font-bold" style="font-size: 14px;">TOTAL</td>
            <td class="text-right font-bold" style="font-size: 14px;">{{ number_format($order->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left uppercase">BAYAR ({{ $order->payment->payment_method }})</td>
            <td class="text-right">{{ number_format($order->payment->amount_paid, 0, ',', '.') }}</td>
        </tr>
        @if($order->payment->payment_method === 'cash')
        <tr>
            <td class="text-left font-bold">KEMBALI</td>
            <td class="text-right font-bold">{{ number_format($order->payment->change, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    {{-- Footer Struk --}}
    <div class="text-center mt-2">
        <p style="margin: 0; font-size: 10px;">{{ $setting->receipt_footer_text ?? 'Terima Kasih Atas Kunjungan Anda' }}</p>
        <p style="margin: 4px 0; font-size: 9px; color: #555;">Scan QR Code Meja untuk pesanan selanjutnya.</p>
    </div>

    <script>
        window.onload = function() { 
            setTimeout(function() { window.print(); }, 600); 
        }
    </script>
</body>
</html>