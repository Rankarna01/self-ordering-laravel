@extends('layouts.app')

@section('title', 'Pembayaran & Kasir')

@section('content')
<div x-data="{ currentTab: 'unpaid' }" class="space-y-6">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-secondary">Sistem Kasir</h2>
            <p class="text-sm text-gray-500">Proses pembayaran tagihan pesanan pelanggan.</p>
        </div>
        
        <div class="flex bg-gray-100/80 p-1.5 rounded-xl border border-gray-200 w-full md:w-auto">
            <button @click="currentTab = 'unpaid'" :class="currentTab === 'unpaid' ? 'bg-white shadow-sm text-primary font-bold' : 'text-gray-500 hover:text-gray-700 font-medium'" class="flex-1 md:flex-none px-5 py-2 rounded-lg text-sm transition">
                Belum Dibayar ({{ $unpaidOrders->count() }})
            </button>
            <button @click="currentTab = 'paid'" :class="currentTab === 'paid' ? 'bg-white shadow-sm text-primary font-bold' : 'text-gray-500 hover:text-gray-700 font-medium'" class="flex-1 md:flex-none px-5 py-2 rounded-lg text-sm transition">
                Riwayat Hari Ini
            </button>
        </div>
    </div>

    <div x-show="currentTab === 'unpaid'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" id="unpaid-container">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse($unpaidOrders as $order)
                <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col h-full relative overflow-hidden group">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-start">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">No. Order</p>
                            <p class="font-bold text-secondary">{{ $order->order_number }}</p>
                        </div>
                        <div class="w-auto min-w-[40px] px-2.5 h-10 rounded-xl {{ $order->order_type === 'take_away' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-primary/10 text-primary' }} flex items-center justify-center font-bold text-xs gap-1">
                            @if($order->order_type === 'take_away') <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> @endif
                            {{ $order->table_number_display }}
                        </div>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div class="mb-4">
                            <p class="text-xs text-gray-500 mb-1">Pelanggan</p>
                            <p class="font-semibold text-secondary">{{ $order->customer_name ?? 'Tamu Umum' }}</p>
                            @if($order->pickup_time)
                                <p class="text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded mt-1.5 border border-purple-100 inline-block">⏰ {{ $order->pickup_time }}</p>
                            @endif
                            @if($order->take_away_notes)
                                <p class="text-[11px] text-purple-700 italic mt-1">📝 "{{ $order->take_away_notes }}"</p>
                            @endif
                        </div>
                        <div class="bg-orange-50 rounded-xl p-4 border border-orange-100">
                            <p class="text-xs text-orange-700 mb-1">Total Tagihan</p>
                            <p class="text-2xl font-bold text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-100">
                        <button onclick="openPaymentModal({{ $order->id }}, '{{ $order->order_number }}', {{ $order->total_amount }})" class="w-full bg-secondary hover:bg-black text-white px-4 py-3 rounded-xl text-sm font-semibold transition flex justify-center items-center gap-2 group-hover:scale-[1.02]">
                            <i data-lucide="banknote" class="w-4 h-4"></i> Proses Pembayaran
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center bg-surface rounded-2xl border border-gray-100 border-dashed text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 text-gray-300">
                        <i data-lucide="check-circle" class="w-10 h-10"></i>
                    </div>
                    <p class="text-secondary font-semibold text-lg">Semua Tagihan Lunas</p>
                    <p class="text-sm text-gray-500 mt-1">Belum ada pesanan aktif yang perlu dibayar.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div x-show="currentTab === 'paid'" style="display: none;" class="bg-surface border border-gray-100 rounded-2xl shadow-sm overflow-hidden" id="paid-container">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50/50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold">No. Order</th>
                        <th class="px-6 py-4 font-semibold text-center">Meja / Tipe</th>
                        <th class="px-6 py-4 font-semibold">Metode</th>
                        <th class="px-6 py-4 font-semibold text-right">Total Tagihan</th>
                        <th class="px-6 py-4 font-semibold text-center">Waktu Bayar</th>
                        <th class="px-6 py-4 font-semibold text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($paidOrders as $order)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 font-bold text-secondary">{{ $order->order_number }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 h-8 rounded-lg {{ $order->order_type === 'take_away' ? 'bg-purple-100 text-purple-700 font-bold text-xs' : 'bg-gray-100 font-bold' }}">
                                @if($order->order_type === 'take_away') 🛍️ @endif
                                {{ $order->table_number_display }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="uppercase font-semibold text-xs {{ $order->payment->payment_method == 'cash' ? 'text-green-600' : 'text-blue-600' }}">
                                {{ $order->payment->payment_method }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-secondary text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center text-xs text-gray-500">{{ $order->payment->created_at->format('H:i') }}</td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.payments.receipt', $order->id) }}" target="_blank" class="p-1.5 inline-block text-gray-500 bg-gray-100 hover:bg-gray-200 hover:text-secondary transition rounded-lg" title="Cetak Struk">
                                <i data-lucide="printer" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada riwayat pembayaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="paymentModal" class="fixed inset-0 z-[100] bg-secondary/60 backdrop-blur-sm hidden flex-col items-center justify-center p-4">
    <div class="bg-surface w-full max-w-md rounded-2xl shadow-2xl border border-gray-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col" id="paymentModalContent">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50">
            <div>
                <h3 class="text-lg font-bold text-secondary flex items-center gap-2">Proses Pembayaran</h3>
                <p class="text-xs text-gray-500 mt-0.5" id="modalOrderNo"></p>
            </div>
            <button onclick="closePaymentModal()" class="text-gray-400 hover:text-danger hover:bg-red-50 p-1.5 rounded-lg transition"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        
        <form id="paymentForm">
            <div class="p-5 space-y-5">
                <input type="hidden" id="pay_order_id">
                <input type="hidden" id="pay_total_amount_raw">

                <div class="bg-primary/10 border border-primary/20 rounded-xl p-5 text-center">
                    <p class="text-sm font-semibold text-primary mb-1">Total Tagihan</p>
                    <p class="text-3xl font-bold text-secondary" id="display_total"></p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-secondary mb-1.5">Metode Pembayaran</label>
                    <select id="payment_method" name="payment_method" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none text-sm text-secondary bg-gray-50 focus:bg-white cursor-pointer" onchange="toggleCashInput()">
                        <option value="cash">💵 Uang Tunai (Cash)</option>
                        <option value="qris">📱 QRIS / E-Wallet</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                    </select>
                </div>

                <div id="cash_input_group">
                    <label class="block text-sm font-semibold text-secondary mb-1.5">Uang Diterima (Rp)</label>
                    <input type="text" id="amount_paid_display" placeholder="0" class="w-full px-4 py-3 text-lg font-bold rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none text-secondary bg-gray-50 focus:bg-white" onkeyup="calculateChange()">
                    <input type="hidden" id="amount_paid" name="amount_paid">
                </div>

                <div id="change_group" class="flex justify-between items-center py-2 px-1 border-t border-gray-100">
                    <span class="text-sm font-semibold text-gray-500">Kembalian:</span>
                    <span class="text-xl font-bold text-success" id="display_change">Rp 0</span>
                </div>
            </div>

            <div class="p-5 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                <button type="submit" id="btnPay" disabled class="w-full px-5 py-3.5 rounded-xl text-sm font-bold text-white bg-gray-300 cursor-not-allowed transition flex items-center justify-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i> Konfirmasi Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let formatRupiah = (number) => { return new Intl.NumberFormat('id-ID').format(number); }

    function openPaymentModal(orderId, orderNo, totalAmount) {
        $('#pay_order_id').val(orderId);
        $('#pay_total_amount_raw').val(totalAmount);
        $('#modalOrderNo').text('Order: ' + orderNo);
        $('#display_total').text('Rp ' + formatRupiah(totalAmount));
        
        $('#paymentForm')[0].reset();
        $('#amount_paid_display').val('');
        $('#amount_paid').val('');
        toggleCashInput();
        
        $('#paymentModal').removeClass('hidden').addClass('flex');
        setTimeout(() => { $('#paymentModalContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100'); }, 10);
    }

    function closePaymentModal() {
        $('#paymentModalContent').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => { $('#paymentModal').removeClass('flex').addClass('hidden'); }, 300);
    }

    // Toggle tampilan input jumlah uang jika non-cash
    function toggleCashInput() {
        let method = $('#payment_method').val();
        let total = parseInt($('#pay_total_amount_raw').val());
        let btn = $('#btnPay');

        if (method === 'cash') {
            $('#cash_input_group').show();
            $('#change_group').show();
            $('#amount_paid').val('');
            $('#amount_paid_display').val('');
            $('#display_change').text('Rp 0').removeClass('text-danger text-success').addClass('text-gray-500');
            btn.prop('disabled', true).removeClass('bg-success hover:bg-green-600 shadow-md').addClass('bg-gray-300 cursor-not-allowed');
        } else {
            // QRIS / Transfer
            $('#cash_input_group').hide();
            $('#change_group').hide();
            $('#amount_paid').val(total); // Auto isi uang pas
            btn.prop('disabled', false).removeClass('bg-gray-300 cursor-not-allowed').addClass('bg-success hover:bg-green-600 shadow-md shadow-green-500/30 text-white');
        }
    }

    // Kalkulasi kembalian realtime saat ngetik uang
    function calculateChange() {
        let displayInput = $('#amount_paid_display');
        let rawValue = displayInput.val().replace(/[^0-9]/g, '');
        let total = parseInt($('#pay_total_amount_raw').val());
        let btn = $('#btnPay');
        
        if(rawValue) {
            displayInput.val(formatRupiah(rawValue));
            $('#amount_paid').val(rawValue);
            
            let change = parseInt(rawValue) - total;
            
            if (change < 0) {
                $('#display_change').text('Uang Kurang!').removeClass('text-success text-gray-500').addClass('text-danger');
                btn.prop('disabled', true).removeClass('bg-success hover:bg-green-600 shadow-md').addClass('bg-gray-300 cursor-not-allowed');
            } else {
                $('#display_change').text('Rp ' + formatRupiah(change)).removeClass('text-danger text-gray-500').addClass('text-success');
                btn.prop('disabled', false).removeClass('bg-gray-300 cursor-not-allowed').addClass('bg-success hover:bg-green-600 shadow-md shadow-green-500/30 text-white');
            }
        } else {
            displayInput.val('');
            $('#amount_paid').val('');
            $('#display_change').text('Rp 0').removeClass('text-danger text-success').addClass('text-gray-500');
            btn.prop('disabled', true).removeClass('bg-success hover:bg-green-600 shadow-md').addClass('bg-gray-300 cursor-not-allowed');
        }
    }

    // Proses Submit Pembayaran AJAX
    $('#paymentForm').submit(function(e) {
        e.preventDefault();
        let orderId = $('#pay_order_id').val();
        
        let btn = $('#btnPay');
        let originalText = btn.html();
        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Memproses...');
        lucide.createIcons();

        $.ajax({
            url: `/admin/payments/${orderId}`,
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                closePaymentModal();
                
                // SweetAlert Success & Buka Tab Cetak Struk
                Swal.fire({
                    icon: 'success',
                    title: 'Lunas!',
                    text: response.message,
                    confirmButtonText: '<i data-lucide="printer" class="w-4 h-4 inline mr-2"></i> Cetak Struk',
                    confirmButtonColor: '#F97316',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(response.print_url, '_blank'); // Buka tab struk
                    }
                    location.reload(); // Refresh halaman (bisa diganti refreshGrid jika dibuat modular)
                });
                lucide.createIcons();
            },
            error: function(xhr) {
                Alert.error(xhr.responseJSON?.message || 'Terjadi kesalahan sistem.');
                btn.prop('disabled', false).html(originalText);
                lucide.createIcons();
            }
        });
    });
</script>
@endpush