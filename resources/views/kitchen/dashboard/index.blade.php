@extends('layouts.kitchen')
@section('title', 'Dapur - Pesanan Masuk')

@section('content')
<div class="space-y-6 h-full flex flex-col">

    <div class="flex justify-between items-center bg-surface p-4 rounded-2xl border border-gray-100 shadow-sm shrink-0">
        <div>
            <h2 class="text-xl font-bold text-secondary">Pesanan Masuk</h2>
            <p class="text-xs text-gray-500">Pesanan baru yang masuk dari pelanggan.</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-xs font-medium text-success">
                <span class="w-2 h-2 rounded-full bg-success animate-pulse"></span> Refresh Otomatis Aktif
            </div>
            <button id="toggleSoundBtn" onclick="toggleSound()" class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-500 hover:text-primary transition border border-gray-200">
                <i data-lucide="volume-2" class="w-5 h-5" id="soundIcon"></i>
            </button>
            <button onclick="toggleFullScreen()" class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-500 hover:text-primary transition border border-gray-200">
                <i data-lucide="maximize" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <div id="kds-board" class="flex-1 flex gap-6 overflow-hidden pb-2" data-order-count="{{ $incomingOrders->count() }}" data-total-items="{{ $incomingOrders->sum(fn($o) => $o->items->sum('quantity')) }}">
        
        <div class="flex-1 flex gap-4 overflow-x-auto custom-scrollbar items-start content-start">
            @forelse($incomingOrders as $order)
                <div class="w-80 shrink-0 bg-surface rounded-2xl border border-gray-100 flex flex-col h-fit max-h-full overflow-hidden shadow-sm border-t-4 {{ $order->order_type === 'take_away' ? 'border-t-purple-600 bg-purple-50/10' : 'border-t-danger' }} relative group">
                    
                    <div class="p-4 border-b border-gray-100 {{ $order->order_type === 'take_away' ? 'bg-purple-50/60' : 'bg-white' }}">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-1.5">
                                <span class="bg-red-50 text-danger text-[10px] font-bold px-2 py-1 rounded-md border border-red-100">BARU</span>
                                @if($order->order_type === 'take_away')
                                    <span class="bg-purple-600 text-white text-[10px] font-black px-2 py-1 rounded-md shadow-sm flex items-center gap-1">
                                        <i data-lucide="shopping-bag" class="w-3 h-3"></i> BUNGKUS
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs font-bold text-gray-400">{{ $order->created_at->format('H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-end mb-1">
                            <h3 class="text-xl font-black {{ $order->order_type === 'take_away' ? 'text-purple-700' : 'text-secondary' }}">{{ $order->table_number_display }}</h3>
                            <p class="text-xs font-bold text-danger">{{ $order->created_at->diffForHumans(null, true) }} lalu</p>
                        </div>
                        <p class="text-xs font-medium text-gray-400">{{ $order->order_number }} • {{ $order->customer_name ?? 'Umum' }}</p>
                        @if($order->pickup_time)
                            <p class="mt-2 text-xs font-bold text-purple-700 bg-purple-100 px-2.5 py-1 rounded-lg inline-block border border-purple-200/60">⏰ Pengambilan: {{ $order->pickup_time }}</p>
                        @endif
                        @if($order->take_away_notes)
                            <p class="mt-1.5 text-xs text-purple-700 italic bg-white p-2 rounded-lg border border-purple-200">📝 "{{ $order->take_away_notes }}"</p>
                        @endif
                    </div>

                    <div class="p-4 flex-1 overflow-y-auto custom-scrollbar space-y-4">
                        @foreach($order->items as $item)
                            <div class="flex gap-3">
                                <span class="font-black text-secondary text-sm">{{ $item->quantity }}x</span>
                                <div>
                                    <p class="font-bold text-secondary text-sm leading-tight">
                                        {{ $item->menu->name }}
                                        @if($item->is_take_away && $order->order_type !== 'take_away')
                                            <span class="ml-1 bg-purple-100 text-purple-700 text-[10px] font-bold px-1.5 py-0.5 rounded border border-purple-200">🛍️ BUNGKUS</span>
                                        @endif
                                    </p>
                                    @if($item->notes)
                                        <div class="mt-1 flex items-start gap-1">
                                            <i data-lucide="alert-circle" class="w-3 h-3 text-primary shrink-0 mt-0.5"></i>
                                            <p class="text-[11px] text-primary italic font-medium">{{ $item->notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                        <p class="text-xs text-gray-500">Total Item: <span class="font-bold text-secondary">{{ $order->items->sum('quantity') }}</span></p>
                        <button onclick="processOrder({{ $order->id }}, 'cooking')" class="bg-danger hover:bg-red-600 text-white text-xs font-bold px-5 py-2.5 rounded-lg transition active:scale-95 shadow-md shadow-red-500/20">
                            Terima Pesanan
                        </button>
                    </div>
                </div>
            @empty
                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 border-2 border-dashed border-gray-200 rounded-2xl">
                    <i data-lucide="coffee" class="w-12 h-12 mb-3 opacity-50"></i>
                    <p class="font-medium">Tidak ada pesanan baru</p>
                </div>
            @endforelse
        </div>

        <div class="w-96 shrink-0 flex flex-col gap-6 overflow-y-auto custom-scrollbar pr-2">
            
            <div class="bg-surface rounded-2xl border border-gray-100 flex flex-col overflow-hidden shadow-sm border-t-4 border-t-primary">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-secondary">Sedang Dimasak</h3>
                        <span class="bg-orange-50 text-primary text-[10px] font-bold px-2 py-0.5 rounded-md border border-orange-100">{{ $cookingOrders->count() }}</span>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-2 max-h-80">
                    @forelse($cookingOrders as $order)
                        <div class="bg-gray-50 border border-gray-100 p-3 rounded-xl flex items-center justify-between group hover:border-primary/50 transition">
                            <div>
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="text-xs font-black {{ $order->order_type === 'take_away' ? 'text-purple-700' : 'text-secondary' }}">{{ $order->table_number_display }}</span>
                                    @if($order->order_type === 'take_away')
                                        <span class="bg-purple-100 text-purple-700 text-[9px] font-bold px-1.5 py-0.5 rounded">🛍️</span>
                                    @endif
                                    <span class="text-[10px] text-gray-400">{{ $order->created_at->format('H:i') }}</span>
                                </div>
                                <p class="text-[10px] text-gray-400 truncate w-32">{{ $order->items->sum('quantity') }} Item</p>
                            </div>
                            <button onclick="processOrder({{ $order->id }}, 'ready')" class="bg-orange-50 hover:bg-primary text-primary hover:text-white text-[10px] font-bold px-4 py-2 rounded-lg transition active:scale-95 border border-orange-100">
                                Selesai Masak
                            </button>
                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-gray-400">Tidak ada proses memasak.</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-surface rounded-2xl border border-gray-100 flex flex-col overflow-hidden shadow-sm border-t-4 border-t-success">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-secondary">Siap Diantar</h3>
                        <span class="bg-green-50 text-success text-[10px] font-bold px-2 py-0.5 rounded-md border border-green-100">{{ $readyOrders->count() }}</span>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-2 max-h-80">
                    @forelse($readyOrders as $order)
                        <div class="bg-gray-50 border border-gray-100 p-3 rounded-xl flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-1">
                                    <span class="text-xs font-black {{ $order->order_type === 'take_away' ? 'text-purple-700' : 'text-secondary' }}">{{ $order->table_number_display }}</span>
                                    @if($order->order_type === 'take_away')
                                        <span class="bg-purple-100 text-purple-700 text-[9px] font-bold px-1 py-0.5 rounded">🛍️</span>
                                    @endif
                                </div>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->order_number }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success"></i>
                                <span class="text-[10px] font-bold text-success">SIAP</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-gray-400">Semua sudah diantar.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let isSoundEnabled = localStorage.getItem('kds_sound_enabled') === 'true';
    let prevOrderCount = {{ $incomingOrders->count() }};
    let prevTotalItems = {{ $incomingOrders->sum(fn($o) => $o->items->sum('quantity')) }};
    let audioCtx = null;

    function getAudioContext() {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    // Fungsi Suara Bel Dapur (Utamakan MP3 Lokal, cadangan Synthesizer Digital)
    function playBellSound() {
        const notifSound = document.getElementById('notifSound');
        if (notifSound) {
            notifSound.currentTime = 0;
            notifSound.play().catch(err => {
                console.log('Memutar synthesizer audio sebagai cadangan');
                playSynthBell();
            });
        } else {
            playSynthBell();
        }
    }

    function playSynthBell() {
        try {
            const ctx = getAudioContext();
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(800, ctx.currentTime);
            gain1.gain.setValueAtTime(0.6, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.8);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start();
            osc1.stop(ctx.currentTime + 0.8);

            setTimeout(() => {
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(600, ctx.currentTime);
                gain2.gain.setValueAtTime(0.6, ctx.currentTime);
                gain2.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 1.2);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start();
                osc2.stop(ctx.currentTime + 1.2);
            }, 200);
        } catch(e) { console.log(e); }
    }

    // Toggle Izin Suara Notifikasi
    function toggleSound() {
        isSoundEnabled = !isSoundEnabled;
        localStorage.setItem('kds_sound_enabled', isSoundEnabled ? 'true' : 'false');
        
        const icon = document.getElementById('soundIcon');
        if(isSoundEnabled) {
            icon.setAttribute('data-lucide', 'volume-2');
            icon.classList.add('text-primary');
            playBellSound(); // Mainkan suara tes saat aktif
        } else {
            icon.setAttribute('data-lucide', 'volume-x');
            icon.classList.remove('text-primary');
        }
        lucide.createIcons();
    }

    // Toggle Layar Penuh
    function toggleFullScreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                alert(`Error: ${err.message}`);
            });
        } else {
            if (document.exitFullscreen) { document.exitFullscreen(); }
        }
    }

    // AJAX Perbarui Status
    function processOrder(orderId, newStatus) {
        $.ajax({
            url: `/kitchen/orders/${orderId}/status`,
            type: 'PUT',
            data: { status: newStatus },
            success: function(res) {
                refreshBoardSilently();
            },
            error: function() {
                Alert.error('Gagal memperbarui status pesanan.');
            }
        });
    }

    // Polling Refresh Otomatis
    function refreshBoardSilently() {
        let originalGlobalAjaxLoader = $.ajaxSettings.global;
        $.ajaxSetup({ global: false }); 

        $.get(window.location.href, function(data) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');
            const newBoard = doc.getElementById('kds-board');
            
            if (newBoard) {
                let currentCount = parseInt(newBoard.getAttribute('data-order-count')) || 0;
                let currentItems = parseInt(newBoard.getAttribute('data-total-items')) || 0;
                
                // Ganti isi dan attribute
                $('#kds-board').html(newBoard.innerHTML);
                $('#kds-board').attr('data-order-count', currentCount);
                $('#kds-board').attr('data-total-items', currentItems);
                lucide.createIcons();
                
                // Jika pesanan bertambah ATAU item menu bertambah dan suara aktif, mainkan bel
                if ((currentCount > prevOrderCount || currentItems > prevTotalItems) && isSoundEnabled) {
                    playBellSound();
                }
                prevOrderCount = currentCount;
                prevTotalItems = currentItems;
            }
        }).always(function() {
            $.ajaxSetup({ global: originalGlobalAjaxLoader });
        });
    }

    $(document).ready(function() {
        const icon = document.getElementById('soundIcon');
        if (isSoundEnabled && icon) {
            icon.setAttribute('data-lucide', 'volume-2');
            icon.classList.add('text-primary');
            lucide.createIcons();
        }

        // Auto-resume audio context saat ada interaksi user di halaman
        $(document).on('click keydown', function() {
            if (isSoundEnabled) {
                try { getAudioContext(); } catch(e){}
            }
        });

        setInterval(refreshBoardSilently, 3000); 
    });
</script>
@endpush