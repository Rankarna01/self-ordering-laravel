@extends('layouts.app')

@section('title', 'Status Pesanan')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-secondary">Status Pesanan</h2>
            <p class="text-sm text-gray-500">Pantau dan kelola seluruh pesanan pelanggan.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="refreshGrid()" class="bg-white border border-gray-200 text-gray-600 hover:text-primary hover:border-primary px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh
            </button>
        </div>
    </div>

    <div id="data-container" class="bg-surface border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50/50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold">No. Order / Waktu</th>
                        <th class="px-6 py-4 font-semibold text-center">Meja</th>
                        <th class="px-6 py-4 font-semibold">Pelanggan</th>
                        <th class="px-6 py-4 font-semibold text-right">Total Tagihan</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/50 transition group">
                        <td class="px-6 py-4">
                            <p class="font-bold text-secondary">{{ $order->order_number }}</p>
                            <p class="text-[11px] text-gray-400 flex items-center gap-1 mt-0.5"><i data-lucide="clock" class="w-3 h-3"></i> {{ $order->created_at->format('d M Y, H:i') }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 h-8 rounded-lg {{ $order->order_type === 'take_away' ? 'bg-purple-100 text-purple-700 font-bold text-xs border border-purple-200' : 'bg-primary/10 text-primary font-bold' }}">
                                @if($order->order_type === 'take_away') 🛍️ @endif
                                {{ $order->table_number_display }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-medium">{{ $order->customer_name ?? 'Tamu Umum' }}</td>
                        <td class="px-6 py-4 font-bold text-secondary text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-orange-50 text-orange-600 border-orange-100',
                                    'cooking' => 'bg-blue-50 text-blue-600 border-blue-100',
                                    'ready' => 'bg-green-50 text-green-600 border-green-100',
                                    'completed' => 'bg-gray-100 text-gray-600 border-gray-200',
                                    'cancelled' => 'bg-red-50 text-red-600 border-red-100'
                                ];
                                $statusText = [
                                    'pending' => 'Menunggu',
                                    'cooking' => 'Dimasak',
                                    'ready' => 'Siap Diantar',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan'
                                ];
                            @endphp
                            <span class="px-3 py-1.5 text-[11px] uppercase tracking-wider font-bold rounded-full border {{ $statusColors[$order->status] }}">
                                {{ $statusText[$order->status] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2 transition-opacity">
                                <button onclick="viewDetail({{ $order->id }})" class="p-1.5 text-primary bg-orange-50 hover:bg-primary hover:text-white transition rounded-lg" title="Detail Pesanan">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                                @if(!in_array($order->status, ['completed', 'cancelled']))
                                <button onclick="openStatusModal({{ $order->id }}, '{{ $order->status }}')" class="p-1.5 text-blue-500 bg-blue-50 hover:bg-blue-500 hover:text-white transition rounded-lg" title="Ubah Status">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mb-3"></i>
                                <p class="font-medium text-secondary">Belum ada pesanan masuk</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="detailModal" class="fixed inset-0 z-[100] bg-secondary/60 backdrop-blur-sm hidden flex-col items-center justify-center p-4">
    <div class="bg-surface w-full max-w-2xl rounded-2xl shadow-2xl border border-gray-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="detailModalContent">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50 shrink-0">
            <div>
                <h3 class="text-lg font-bold text-secondary flex items-center gap-2" id="detailTitle">Detail Pesanan</h3>
                <p class="text-xs text-gray-500 mt-1" id="detailSubtitle"></p>
            </div>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-danger hover:bg-red-50 p-1.5 rounded-lg transition"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        
        <div class="p-5 overflow-y-auto custom-scrollbar flex-1">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="pb-3 font-medium">Item Menu</th>
                        <th class="pb-3 font-medium text-center">Qty</th>
                        <th class="pb-3 font-medium text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="detailItemsBody" class="divide-y divide-gray-50">
                    </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100 bg-gray-50/50 flex justify-between items-center shrink-0">
            <span class="text-sm font-semibold text-gray-500">Total Tagihan:</span>
            <span class="text-xl font-bold text-primary" id="detailTotalAmount"></span>
        </div>
    </div>
</div>

<div id="statusModal" class="fixed inset-0 z-[100] bg-secondary/60 backdrop-blur-sm hidden flex-col items-center justify-center p-4">
    <div class="bg-surface w-full max-w-sm rounded-2xl shadow-2xl border border-gray-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="statusModalContent">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-lg font-bold text-secondary flex items-center gap-2">Ubah Status</h3>
            <button type="button" onclick="closeStatusModal()" class="text-gray-400 hover:text-danger hover:bg-red-50 p-1.5 rounded-lg transition"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="statusForm">
            <div class="p-5 space-y-4">
                <input type="hidden" id="status_order_id" name="id">
                <input type="hidden" name="_method" value="PUT">
                
                <div>
                    <label class="block text-sm font-semibold text-secondary mb-1.5">Pilih Status Baru</label>
                    <select id="update_status" name="status" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none text-sm text-secondary bg-gray-50 focus:bg-white cursor-pointer">
                        <option value="pending">🟠 Menunggu (Pending)</option>
                        <option value="cooking">🔵 Dimasak (Cooking)</option>
                        <option value="ready">🟢 Siap Diantar (Ready)</option>
                        <option value="completed">⚫ Selesai (Completed)</option>
                        <option value="cancelled">🔴 Dibatalkan (Cancelled)</option>
                    </select>
                </div>
            </div>
            <div class="p-5 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                <button type="button" onclick="closeStatusModal()" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-gray-100 transition">Batal</button>
                <button type="submit" id="btnSaveStatus" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-primary hover:bg-[#EA580C] transition shadow-md shadow-primary/30 flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Terapkan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function refreshGrid() {
        $('#data-container').load(window.location.href + ' #data-container > *', function() {
            lucide.createIcons();
        });
    }

    // --- LOGIC MODAL DETAIL ---
    function viewDetail(id) {
        $('#detailItemsBody').html('<tr><td colspan="3" class="text-center py-8"><i data-lucide="loader-2" class="w-8 h-8 animate-spin mx-auto text-primary"></i></td></tr>');
        lucide.createIcons();
        
        $('#detailModal').removeClass('hidden').addClass('flex');
        setTimeout(() => { $('#detailModalContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100'); }, 10);

        $.get(`/admin/orders/${id}`, function(data) {
            $('#detailTitle').text('Order: ' + data.order_number);
            let tableText = data.order_type === 'take_away' ? '🛍️ Take Away / Bungkus' : 'Meja ' + (data.table ? data.table.table_number : 'Counter');
            $('#detailSubtitle').text(`${tableText} • ${data.customer_name ?? 'Tamu Umum'}`);
            $('#detailTotalAmount').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.total_amount));
            
            let itemsHtml = '';
            if (data.items.length === 0) {
                itemsHtml = '<tr><td colspan="3" class="text-center py-4 text-gray-500">Tidak ada item</td></tr>';
            } else {
                data.items.forEach(item => {
                    let notesHtml = item.notes ? `<p class="text-[10px] text-danger mt-1"><i data-lucide="alert-circle" class="w-3 h-3 inline"></i> ${item.notes}</p>` : '';
                    let takeawayBadge = item.is_take_away && data.order_type !== 'take_away' ? `<span class="ml-1 bg-purple-100 text-purple-700 text-[10px] font-bold px-1.5 py-0.5 rounded border border-purple-200">🛍️ BUNGKUS</span>` : '';
                    itemsHtml += `
                        <tr class="hover:bg-gray-50">
                            <td class="py-4">
                                <p class="font-semibold text-secondary">${item.menu.name} ${takeawayBadge}</p>
                                <p class="text-xs text-gray-400">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</p>
                                ${notesHtml}
                            </td>
                            <td class="py-4 text-center font-bold text-secondary">x${item.quantity}</td>
                            <td class="py-4 text-right font-bold text-secondary">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                        </tr>
                    `;
                });
            }
            $('#detailItemsBody').html(itemsHtml);
            lucide.createIcons();
        });
    }

    function closeDetailModal() {
        $('#detailModalContent').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => { $('#detailModal').removeClass('flex').addClass('hidden'); }, 300);
    }

    // --- LOGIC MODAL STATUS ---
    function openStatusModal(id, currentStatus) {
        $('#status_order_id').val(id);
        $('#update_status').val(currentStatus);
        
        $('#statusModal').removeClass('hidden').addClass('flex');
        setTimeout(() => { $('#statusModalContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100'); }, 10);
    }

    function closeStatusModal() {
        $('#statusModalContent').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => { $('#statusModal').removeClass('flex').addClass('hidden'); }, 300);
    }

    $('#statusForm').submit(function(e) {
        e.preventDefault();
        let id = $('#status_order_id').val();
        
        let btn = $('#btnSaveStatus');
        let originalText = btn.html();
        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Menyimpan...');
        lucide.createIcons();

        $.ajax({
            url: `/admin/orders/${id}/status`,
            type: 'POST', // Spoofed to PUT via input hidden
            data: $(this).serialize(),
            success: function(response) {
                closeStatusModal();
                Alert.success(response.message);
                refreshGrid();
            },
            error: function() {
                Alert.error('Terjadi kesalahan sistem.');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
                lucide.createIcons();
            }
        });
    });
</script>
@endpush