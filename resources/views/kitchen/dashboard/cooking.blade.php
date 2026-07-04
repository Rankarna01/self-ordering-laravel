@extends('layouts.kitchen')
@section('title', 'Dapur - Sedang Dimasak')

@section('content')
<div class="space-y-6 flex flex-col h-full" x-data="{ search: '' }">

    <div class="flex justify-between items-center bg-surface p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-50 text-primary rounded-xl flex items-center justify-center border border-orange-100">
                <i data-lucide="flame" class="w-6 h-6 animate-pulse"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-secondary">Antrean Masakan</h2>
                <p class="text-xs text-gray-500">Daftar pesanan yang sedang diproses di dapur.</p>
            </div>
        </div>
        
        <div class="relative w-64">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" x-model="search" placeholder="Cari meja atau menu..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-2 text-sm text-secondary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
    </div>

    <div class="flex-1 bg-surface rounded-2xl border border-gray-100 overflow-hidden flex flex-col shadow-sm">
        <div class="overflow-x-auto flex-1 custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-gray-50 z-10">
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Durasi</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Meja</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Daftar Menu & Catatan</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/50 transition group" 
                        x-show="'{{ $order->table_number_display }} {{ $order->customer_name }}'.toLowerCase().includes(search.toLowerCase())">
                        
                        <td class="px-6 py-6">
                            <div class="flex flex-col">
                                <span class="text-lg font-black text-primary">{{ $order->updated_at->format('H:i') }}</span>
                                <span class="text-[10px] text-gray-400 font-medium italic">Mulai masak {{ $order->updated_at->diffForHumans(null, true) }} lalu</span>
                            </div>
                        </td>

                        <td class="px-6 py-6">
                            <div class="flex items-center gap-3">
                                <div class="w-auto min-w-[40px] px-2.5 h-10 rounded-lg {{ $order->order_type === 'take_away' ? 'bg-purple-100 text-purple-700 border-purple-200 shadow-sm' : 'bg-gray-50 text-secondary border-gray-200' }} border flex items-center justify-center font-bold text-xs gap-1">
                                    @if($order->order_type === 'take_away') <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> @endif
                                    {{ $order->table_number_display }}
                                </div>
                                <div>
                                    <p class="font-bold text-secondary text-sm">{{ $order->customer_name ?? 'Tamu Umum' }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $order->order_number }}</p>
                                    @if($order->pickup_time)
                                        <p class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded mt-1 border border-purple-100 inline-block">⏰ {{ $order->pickup_time }}</p>
                                    @endif
                                    @if($order->take_away_notes)
                                        <p class="text-[10px] text-purple-700 italic mt-0.5">📝 "{{ $order->take_away_notes }}"</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-6">
                            <div class="space-y-3">
                                @foreach($order->items as $item)
                                <div class="flex items-start gap-2">
                                    <span class="bg-gray-100 text-secondary text-[10px] font-bold px-1.5 py-0.5 rounded shrink-0 border border-gray-200">{{ $item->quantity }}x</span>
                                    <div>
                                        <p class="text-sm font-semibold text-secondary leading-none">
                                            {{ $item->menu->name }}
                                            @if($item->is_take_away && $order->order_type !== 'take_away')
                                                <span class="ml-1 bg-purple-100 text-purple-700 text-[9px] font-bold px-1.5 py-0.5 rounded border border-purple-200">🛍️ BUNGKUS</span>
                                            @endif
                                        </p>
                                        @if($item->notes)
                                            <p class="text-[11px] text-primary mt-1 flex items-center gap-1 font-medium italic">
                                                <i data-lucide="info" class="w-3 h-3"></i> {{ $item->notes }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </td>

                        <td class="px-6 py-6 text-center">
                            <button onclick="finishCooking({{ $order->id }})" class="bg-green-50 hover:bg-success text-success hover:text-white font-bold px-6 py-3 rounded-xl transition border border-green-100 flex items-center gap-2 mx-auto active:scale-95">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                Selesai Masak
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center text-gray-300">
                                <i data-lucide="chef-hat" class="w-16 h-16 mb-4"></i>
                                <p class="text-lg font-medium">Belum ada pesanan yang sedang dimasak.</p>
                                <p class="text-xs text-gray-400">Ambil pesanan dari tab 'Pesanan Masuk'.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center text-[10px] text-gray-400 font-bold uppercase tracking-widest">
            <span>Menampilkan {{ $orders->count() }} Pesanan Aktif</span>
            <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> Refresh Otomatis dalam 10 detik</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function finishCooking(id) {
        $.ajax({
            url: `/kitchen/orders/${id}/status`,
            type: 'PUT',
            data: { status: 'ready' },
            success: function(res) {
                location.reload(); 
            },
            error: function() {
                Alert.error('Gagal memperbarui status.');
            }
        });
    }

    // Refresh Otomatis Setiap 15 Detik
    $(document).ready(function() {
        setInterval(() => {
            location.reload();
        }, 15000);
    });
</script>
@endpush