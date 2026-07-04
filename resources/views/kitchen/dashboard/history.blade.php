@extends('layouts.kitchen')
@section('title', 'Dapur - Riwayat Masakan')

@section('content')
<div class="space-y-6 flex flex-col h-full">

    <div class="bg-surface p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gray-100 text-gray-500 rounded-xl flex items-center justify-center border border-gray-200">
                <i data-lucide="history" class="w-6 h-6"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-secondary">Riwayat Masakan</h2>
                <p class="text-xs text-gray-500">Rekapitulasi pesanan yang telah diselesaikan.</p>
            </div>
        </div>
        
        <form method="GET" action="{{ route('kitchen.history') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative">
                <input type="date" name="date" value="{{ request('date', now()->format('Y-m-d')) }}" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm text-secondary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition cursor-pointer">
            </div>
            <div class="relative w-full md:w-56">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pesanan..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-2 text-sm text-secondary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            </div>
            <button type="submit" class="bg-primary hover:bg-[#EA580C] text-white font-bold px-4 py-2 rounded-xl transition flex items-center gap-2 shadow-md shadow-primary/20">
                <i data-lucide="filter" class="w-4 h-4"></i> Filter
            </button>
            <a href="{{ route('kitchen.history') }}" class="text-gray-400 hover:text-secondary px-3 py-2 text-sm font-medium transition">
                Reset
            </a>
        </form>
    </div>

    <div class="flex-1 bg-surface rounded-2xl border border-gray-100 overflow-hidden flex flex-col shadow-sm">
        <div class="overflow-x-auto flex-1 custom-scrollbar">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="sticky top-0 bg-gray-50 z-10">
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest w-48">Waktu Selesai</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Detail Pesanan</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest text-center">Item</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/50 transition">
                        
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-secondary">{{ $order->updated_at->format('d M Y') }}</span>
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $order->updated_at->format('H:i') }} WIB</p>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-auto min-w-[40px] px-2.5 h-10 rounded-lg {{ $order->order_type === 'take_away' ? 'bg-purple-100 text-purple-700 border-purple-200' : 'bg-gray-50 text-secondary border-gray-200' }} border flex items-center justify-center font-bold text-xs gap-1 shrink-0">
                                    @if($order->order_type === 'take_away') <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> @endif
                                    {{ $order->table_number_display }}
                                </div>
                                <div>
                                    <p class="font-bold text-secondary text-sm">{{ $order->order_number }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $order->customer_name ?? 'Tamu Umum' }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span class="bg-gray-100 text-secondary px-3 py-1 rounded-md text-xs font-bold border border-gray-200">
                                {{ $order->items->sum('quantity') }} Porsi
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if($order->status === 'completed')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 text-success border border-green-100 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                    <i data-lucide="check-check" class="w-3 h-3"></i> Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-danger border border-red-100 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                    <i data-lucide="x-circle" class="w-3 h-3"></i> Dibatalkan
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center text-gray-300">
                                <i data-lucide="folder-search" class="w-16 h-16 mb-4"></i>
                                <p class="text-lg font-medium">Tidak ada riwayat masakan.</p>
                                <p class="text-xs text-gray-400">Coba ubah tanggal atau kata kunci pencarian.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 bg-gray-50 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<style>
    nav[aria-label="Pagination"] p { color: #9CA3AF !important; font-size: 12px; }
    nav[aria-label="Pagination"] span[aria-current="page"] span { 
        background-color: #F97316 !important; 
        color: #fff !important; 
        border-color: #F97316 !important; 
    }
    nav[aria-label="Pagination"] a, nav[aria-label="Pagination"] span.relative { 
        background-color: #FFFFFF !important; 
        border-color: #E5E7EB !important; 
        color: #374151 !important; 
    }
    nav[aria-label="Pagination"] a:hover { 
        background-color: #F9FAFB !important; 
    }
    nav[aria-label="Pagination"] svg { stroke: currentColor; }
</style>
@endsection