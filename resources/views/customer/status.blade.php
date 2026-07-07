@extends('layouts.customer')
@section('title', 'Status Pesanan')

@section('content')
<div x-data="orderTracker('{{ $order->status }}', '{{ $order->updated_at->format('H:i') }}', '{{ route('customer.checkStatus', $order->order_number) }}')" 
     x-init="startTracking()" 
     class="flex flex-col min-h-screen bg-white relative pb-20"> <div class="sticky top-0 z-40 bg-white px-5 py-4 flex items-center justify-between border-b border-gray-100">
        <a href="{{ route('customer.menu', ['table' => $tableNumber]) }}" class="p-2 -ml-2 text-secondary hover:bg-gray-100 rounded-full transition">
            <i data-lucide="chevron-left" class="w-6 h-6"></i>
        </a>
        <h1 class="text-lg font-bold text-secondary">Status Pesanan</h1>
        <div class="w-10"></div>
    </div>

    <div class="flex-1 flex flex-col p-6 overflow-y-auto custom-scrollbar">
        
        <div class="flex flex-col items-center text-center mt-4 mb-8">
            
            <div class="w-48 h-48 relative flex items-center justify-center mb-6">
                
                <svg class="absolute inset-0 w-full h-full transform -rotate-90 z-0" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="47" fill="transparent" stroke="#F3F4F6" stroke-width="4" />
                    <circle cx="50" cy="50" r="47" fill="transparent" stroke="#F97316" stroke-width="4" stroke-dasharray="295" :stroke-dashoffset="getProgressOffset()" class="transition-all duration-1000 ease-out" />
                </svg>
                
                <div x-show="currentStatus === 'pending'" style="display: none;" class="relative z-10 w-32 h-32 flex items-center justify-center">
                    <lottie-player src="{{ asset('Success burst.json') }}" background="transparent" speed="1" style="width: 100%; height: 100%; transform: scale(1.5);" loop autoplay></lottie-player>
                </div>

                <div x-show="currentStatus === 'cooking'" style="display: none;" class="relative z-10 w-32 h-32 flex items-center justify-center">
                    <lottie-player src="{{ asset('Cooking at home character.json') }}" background="transparent" speed="1" style="width: 100%; height: 100%; transform: scale(1.5);" loop autoplay></lottie-player>
                </div>

                <div x-show="currentStatus === 'ready'" style="display: none;" class="relative z-10 w-32 h-32 flex items-center justify-center">
                    <lottie-player src="{{ asset('Food Delivery Lottie Animation.json') }}" background="transparent" speed="1" style="width: 100%; height: 100%; transform: scale(1.5);" loop autoplay></lottie-player>
                </div>

                <div x-show="currentStatus === 'completed'" style="display: none;" class="relative z-10 w-28 h-28 bg-green-50 rounded-full flex items-center justify-center border-4 border-white shadow-inner">
                    <i data-lucide="check-circle-2" class="w-14 h-14 text-success animate-[bounce_1s_ease-out]"></i>
                </div>

                <div x-show="currentStatus === 'cancelled'" style="display: none;" class="relative z-10 w-28 h-28 bg-red-50 rounded-full flex items-center justify-center border-4 border-white shadow-inner">
                    <i data-lucide="x-circle" class="w-14 h-14 text-danger animate-[pulse_1s_ease-out_infinite]"></i>
                </div>

            </div>

            <h2 class="text-2xl font-extrabold text-secondary mb-2" x-text="getStatusTitle()"></h2>
            <p class="text-sm text-gray-500 px-4" x-text="getStatusDesc()"></p>
            
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                <span class="bg-gray-100 text-secondary text-xs font-bold px-3 py-1.5 rounded-full border border-gray-200 shadow-sm">
                    #{{ $order->order_number }}
                </span>
                <span class="{{ $order->order_type === 'take_away' ? 'bg-purple-100 text-purple-700 border-purple-200' : 'bg-orange-100 text-primary border-orange-200' }} text-xs font-bold px-3 py-1.5 rounded-full border shadow-sm flex items-center gap-1">
                    <i data-lucide="{{ $order->order_type === 'take_away' ? 'shopping-bag' : 'utensils' }}" class="w-3.5 h-3.5"></i> {{ $order->table_number_display }}
                </span>
            </div>
            
            <div x-show="currentStatus === 'cancelled'" style="display: none;" class="mt-4 p-3 bg-red-50 text-danger rounded-xl text-sm font-semibold border border-red-100">
                Mohon maaf, pesanan dibatalkan. Silakan hubungi pelayan.
            </div>
        </div>

        <div class="px-2" x-show="currentStatus !== 'cancelled'">
            <div class="relative space-y-6 before:absolute before:inset-0 before:ml-4 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-gray-200 before:to-gray-100">
                
                <div class="relative flex items-center gap-4">
                    <div :class="stepReached(1) ? 'bg-success border-success' : 'bg-white border-gray-300'" class="w-8 h-8 rounded-full border-2 flex items-center justify-center shadow-sm z-10 transition-colors duration-500 shrink-0">
                        <i x-show="stepReached(1)" data-lucide="check" class="w-4 h-4 text-white"></i>
                    </div>
                    <div class="flex-1 flex justify-between items-center">
                        <h4 :class="stepReached(1) ? 'text-secondary font-bold' : 'text-gray-400 font-medium'" class="text-sm transition-colors">Pesanan Diterima</h4>
                        <span x-show="stepReached(1)" class="text-xs text-gray-500 font-medium">{{ $order->created_at->format('H:i') }}</span>
                    </div>
                </div>

                <div class="relative flex items-center gap-4">
                    <div :class="stepActive(2) ? 'bg-warning border-warning ring-4 ring-orange-50' : (stepReached(2) ? 'bg-success border-success' : 'bg-white border-gray-300')" class="w-8 h-8 rounded-full border-2 flex items-center justify-center shadow-sm z-10 transition-all duration-500 shrink-0">
                        <i x-show="stepReached(3)" data-lucide="check" class="w-4 h-4 text-white"></i>
                        <div x-show="stepActive(2)" class="w-2.5 h-2.5 bg-white rounded-full animate-pulse"></div>
                    </div>
                    <div class="flex-1 flex justify-between items-center">
                        <h4 :class="stepActive(2) ? 'text-secondary font-bold text-base' : (stepReached(2) ? 'text-secondary font-bold' : 'text-gray-400 font-medium')" class="text-sm transition-colors">Sedang Dimasak</h4>
                        <span x-show="stepReached(2)" class="text-xs text-gray-500 font-medium" x-text="stepReached(2) ? lastUpdate : ''"></span>
                    </div>
                </div>

                <div class="relative flex items-center gap-4">
                    <div :class="stepActive(3) ? 'bg-warning border-warning ring-4 ring-orange-50' : (stepReached(3) ? 'bg-success border-success' : 'bg-white border-gray-300')" class="w-8 h-8 rounded-full border-2 flex items-center justify-center shadow-sm z-10 transition-all duration-500 shrink-0">
                        <i x-show="stepReached(4)" data-lucide="check" class="w-4 h-4 text-white"></i>
                        <div x-show="stepActive(3)" class="w-2.5 h-2.5 bg-white rounded-full animate-pulse"></div>
                    </div>
                    <div class="flex-1 flex justify-between items-center">
                        <h4 :class="stepActive(3) ? 'text-secondary font-bold text-base' : (stepReached(3) ? 'text-secondary font-bold' : 'text-gray-400 font-medium')" class="text-sm transition-colors">Siap Diantar</h4>
                        <span x-show="stepReached(3)" class="text-xs text-gray-500 font-medium" x-text="stepReached(3) ? lastUpdate : ''"></span>
                    </div>
                </div>

                <div class="relative flex items-center gap-4">
                    <div :class="stepReached(4) ? 'bg-success border-success ring-4 ring-green-50' : 'bg-white border-gray-300'" class="w-8 h-8 rounded-full border-2 flex items-center justify-center shadow-sm z-10 transition-colors duration-500 shrink-0">
                        <i x-show="stepReached(4)" data-lucide="check" class="w-4 h-4 text-white"></i>
                    </div>
                    <div class="flex-1 flex justify-between items-center">
                        <h4 :class="stepReached(4) ? 'text-success font-bold text-base' : 'text-gray-400 font-medium'" class="text-sm transition-colors">Selesai</h4>
                        <span x-show="stepReached(4)" class="text-xs text-success font-bold" x-text="stepReached(4) ? lastUpdate : ''"></span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="bg-white border-t border-gray-100 p-5 pb-8 shrink-0 relative z-20">
        <button @click="isDetailOpen = true" class="w-full bg-white border-2 border-gray-200 text-secondary py-4 rounded-2xl font-bold hover:bg-gray-50 hover:border-gray-300 transition shadow-sm active:scale-95">
            Lihat Detail Pesanan
        </button>
    </div>

    <div x-show="isDetailOpen" style="display: none;" class="fixed inset-0 z-[60] flex flex-col justify-end max-w-[414px] mx-auto">
        <div @click="isDetailOpen = false" x-show="isDetailOpen" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        
        <div x-show="isDetailOpen" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" class="relative bg-white w-full rounded-t-3xl h-[70vh] flex flex-col overflow-hidden">
            
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="font-bold text-secondary text-lg">Detail Pesanan</h3>
                    <p class="text-xs text-gray-500">Order: {{ $order->order_number }}</p>
                </div>
                <button @click="isDetailOpen = false" class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-500 active:scale-90"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 custom-scrollbar pb-10">
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex justify-between items-start gap-3">
                            <div class="w-14 h-14 bg-gray-100 rounded-xl overflow-hidden shrink-0 border border-gray-100">
                                @if($item->menu->image) <img src="{{ Storage::url($item->menu->image) }}" class="w-full h-full object-cover"> @else <div class="w-full h-full flex items-center justify-center text-gray-300"><i data-lucide="image" class="w-5 h-5"></i></div> @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-secondary text-sm">{{ $item->menu->name }}</h4>
                                @if($item->notes) <p class="text-[10px] text-primary italic font-medium mt-0.5"><i data-lucide="info" class="w-3 h-3 inline"></i> {{ $item->notes }}</p> @endif
                                <p class="text-xs font-bold text-gray-500 mt-1">{{ $item->quantity }}x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                            <div class="font-black text-secondary text-sm text-right shrink-0">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6 pt-4 border-t border-dashed border-gray-200 space-y-2">
                    <div class="flex justify-between text-sm"><span class="text-gray-500 font-medium">Subtotal</span><span class="font-bold text-secondary">Rp {{ number_format($order->total_amount / 1.1, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500 font-medium">Pajak (10%)</span><span class="font-bold text-secondary">Rp {{ number_format($order->total_amount - ($order->total_amount / 1.1), 0, ',', '.') }}</span></div>
                    <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                        <span class="text-gray-500 font-bold">Total Tagihan</span>
                        <span class="font-black text-primary text-xl">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-100 mt-4">
                        <span class="text-xs text-gray-500 font-bold uppercase">Status Pembayaran</span>
                        <span class="{{ $order->payment_status == 'paid' ? 'bg-success/20 text-success' : 'bg-danger/10 text-danger' }} px-3 py-1 rounded-full text-[10px] font-black tracking-wider uppercase">
                            {{ $order->payment_status == 'paid' ? 'LUNAS' : 'BELUM DIBAYAR' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('orderTracker', (initialStatus, initialTime, checkUrl) => ({
            currentStatus: initialStatus,
            lastUpdate: initialTime,
            isDetailOpen: false,

            statusOrder: { 'pending': 1, 'cooking': 2, 'ready': 3, 'completed': 4, 'cancelled': -1 },

            startTracking() {
                setInterval(() => {
                    if(this.currentStatus !== 'completed' && this.currentStatus !== 'cancelled') {
                        fetch(checkUrl)
                            .then(res => res.json())
                            .then(data => {
                                this.currentStatus = data.status;
                                this.lastUpdate = data.time;
                            })
                            .catch(err => console.error("Tracking Error:", err));
                    }
                }, 5000); // Dipercepat menjadi 5 detik agar animasi lebih responsif
            },

            getProgressOffset() {
                let step = this.statusOrder[this.currentStatus] || 0;
                if (step < 1) return 295; // Cancel
                let percentage = (step / 4) * 100;
                return 295 - (295 * percentage) / 100;
            },

            stepReached(step) {
                let currentStep = this.statusOrder[this.currentStatus] || 0;
                return currentStep >= step;
            },

            stepActive(step) {
                let currentStep = this.statusOrder[this.currentStatus] || 0;
                return currentStep === step;
            },

            getStatusTitle() {
                const titles = { 'pending': 'Pesanan Diterima', 'cooking': 'Sedang Dimasak', 'ready': 'Siap Diantar', 'completed': 'Pesanan Selesai!', 'cancelled': 'Dibatalkan' };
                return titles[this.currentStatus] || 'Memuat...';
            },

            getStatusDesc() {
                const descs = { 
                    'pending': 'Pesanan Anda sedang menunggu konfirmasi dapur.', 
                    'cooking': 'Koki kami sedang menyiapkan hidangan lezat Anda. \uD83D\uDC68\u200D\uD83C\uDF73', 
                    'ready': 'Yeay! Makanan Anda sedang diantar oleh pelayan. \uD83D\uDEB2', 
                    'completed': 'Pesanan telah selesai. Selamat menikmati hidangan Anda! \uD83C\uDF7D\uFE0F',
                    'cancelled': ''
                };
                return descs[this.currentStatus] || '';
            }
        }));
    });
</script>
@endpush