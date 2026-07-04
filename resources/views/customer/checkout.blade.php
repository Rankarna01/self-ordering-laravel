@extends('layouts.customer')
@section('title', 'Checkout')

@section('content')
<div x-data="checkoutApp()" x-init="initCheckout()" class="flex flex-col min-h-screen bg-gray-50 pb-40 relative">
    
    {{-- Header --}}
    <div class="sticky top-0 z-40 bg-white px-5 py-4 flex items-center justify-between border-b border-gray-100 shadow-sm">
        <a href="{{ route('customer.menu', ['table' => $table->table_number]) }}" class="p-2 -ml-2 text-secondary hover:bg-gray-100 rounded-full transition"><i data-lucide="chevron-left" class="w-6 h-6"></i></a>
        <h1 class="text-lg font-bold text-secondary">Checkout</h1>
        <div class="w-10"></div>
    </div>

    {{-- Keranjang Kosong --}}
    <div x-show="cart.length === 0" style="display: none;" class="flex-1 flex flex-col items-center justify-center p-8 text-center mt-20">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <i data-lucide="shopping-cart" class="w-12 h-12 text-gray-300"></i>
        </div>
        <h2 class="text-xl font-bold text-secondary mb-2">Keranjang Kosong</h2>
        <p class="text-sm text-gray-500 mb-6">Anda belum memilih menu apapun.</p>
        <a href="{{ route('customer.menu', ['table' => $table->table_number]) }}" class="bg-primary hover:bg-[#EA580C] text-white px-6 py-3 rounded-xl font-bold shadow-md shadow-primary/30 transition active:scale-95">Kembali Pilih Menu</a>
    </div>

    <div x-show="cart.length > 0" class="p-5 space-y-5">

        {{-- ========== LOADING STATE ========== --}}
        <div x-show="isCheckingOrder" class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center justify-center gap-3">
            <div class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm text-gray-500 font-medium">Memeriksa sesi meja...</p>
        </div>

        {{-- ========== RETURNING CUSTOMER BANNER (jika ada order aktif) ========== --}}
        {{-- ========== RETURNING CUSTOMER BANNER (jika ada order aktif & pilih Dine-In) ========== --}}
        <div x-show="!isCheckingOrder && hasActiveOrder && orderType === 'dine_in'" x-transition class="relative overflow-hidden bg-gradient-to-br from-primary to-orange-500 p-5 rounded-3xl shadow-lg shadow-primary/30">
            {{-- Decorative circle --}}
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-white/10 rounded-full"></div>
            
            <div class="relative flex items-start gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i data-lucide="user-check" class="w-6 h-6 text-white"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white/80 text-xs font-semibold uppercase tracking-wider mb-0.5">Sesi Aktif Terdeteksi</p>
                    <h3 class="text-white font-black text-lg leading-tight truncate" x-text="customerName"></h3>
                    <p class="text-white/70 text-xs mt-1">
                        <span x-show="customerPhone" x-text="'📞 ' + customerPhone"></span>
                    </p>
                    <div class="flex items-center gap-2 mt-3">
                        <span class="bg-white/20 text-white text-[10px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1">
                            <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                            <span x-text="existingItemsCount + ' item sebelumnya'"></span>
                        </span>
                        <span class="bg-white/20 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">#<span x-text="existingOrderNumber"></span></span>
                    </div>
                </div>
            </div>

            {{-- Info tambahan --}}
            <div class="relative mt-4 bg-white/15 rounded-2xl px-4 py-3 flex items-start gap-2">
                <i data-lucide="info" class="w-4 h-4 text-white flex-shrink-0 mt-0.5"></i>
                <p class="text-white/90 text-xs leading-relaxed">
                    Pesanan baru ini akan <strong>digabungkan</strong> ke tagihan yang sudah ada. Anda tetap dalam 1 bill yang sama.
                </p>
            </div>
        </div>

        {{-- ========== PILIH TIPE PESANAN ========== --}}
        <div x-show="!isCheckingOrder" x-transition class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-secondary mb-3 text-sm flex items-center gap-2">
                <i data-lucide="shopping-bag" class="w-4 h-4 text-primary"></i> Tipe Pesanan
            </h3>
            <div class="grid grid-cols-2 gap-3">
                {{-- Card Dine In --}}
                <div @click="orderType = 'dine_in'" 
                     :class="orderType === 'dine_in' ? 'border-primary bg-orange-50/50 text-primary shadow-sm' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                     class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col items-center text-center gap-1.5 relative overflow-hidden">
                    <div :class="orderType === 'dine_in' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'" class="w-10 h-10 rounded-xl flex items-center justify-center transition">
                        <i data-lucide="utensils" class="w-5 h-5"></i>
                    </div>
                    <span class="font-bold text-sm text-secondary">Makan di Tempat</span>
                    <span x-show="tableId && tableId !== 'null' && tableNumber !== 'Counter / Bungkus' && tableNumber !== 'Counter'" class="text-[11px] font-medium text-gray-400" x-text="'Meja ' + tableNumber"></span>
                    
                    <div x-show="orderType === 'dine_in'" class="absolute top-2 right-2 text-primary">
                        <i data-lucide="check-circle-2" class="w-4 h-4 fill-primary text-white"></i>
                    </div>
                </div>

                {{-- Card Take Away --}}
                <div @click="orderType = 'take_away'" 
                     :class="orderType === 'take_away' ? 'border-primary bg-orange-50/50 text-primary shadow-sm' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                     class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col items-center text-center gap-1.5 relative overflow-hidden">
                    <div :class="orderType === 'take_away' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'" class="w-10 h-10 rounded-xl flex items-center justify-center transition">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </div>
                    <span class="font-bold text-sm text-secondary">Bawa Pulang</span>

                    <div x-show="orderType === 'take_away'" class="absolute top-2 right-2 text-primary">
                        <i data-lucide="check-circle-2" class="w-4 h-4 fill-primary text-white"></i>
                    </div>
                </div>
            </div>

            {{-- Catatan Take Away --}}
            <div x-show="orderType === 'take_away'" x-transition class="mt-4 pt-4 border-t border-dashed border-gray-200">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Catatan Bungkus <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <input type="text" x-model="takeAwayNotes" placeholder="Misal: Minta sendok plastik, saus pisah" class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm text-secondary bg-gray-50 focus:bg-white transition">
                </div>
            </div>
        </div>

        {{-- ========== FORM INPUT (jika belum ada order aktif atau pilih Take Away) ========== --}}
        <div x-show="!isCheckingOrder && (!hasActiveOrder || orderType === 'take_away')" x-transition class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-secondary mb-4 text-sm flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-primary"></i> Informasi Pemesan
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Nama Lengkap <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        x-model="customerName" 
                        placeholder="Masukkan nama Anda"
                        class="w-full px-4 py-3.5 rounded-2xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm text-secondary bg-gray-50 focus:bg-white transition"
                    >
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Nomor HP <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <input 
                        type="tel" 
                        x-model="customerPhone" 
                        placeholder="08xxxxxxxx"
                        class="w-full px-4 py-3.5 rounded-2xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm text-secondary bg-gray-50 focus:bg-white transition"
                    >
                </div>
            </div>
        </div>

        {{-- ========== RINGKASAN PESANAN BARU ========== --}}
        <div x-show="!isCheckingOrder" x-transition class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-secondary mb-4 text-sm flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-primary"></i> 
                <span x-text="(hasActiveOrder && orderType === 'dine_in') ? 'Pesanan Tambahan' : 'Ringkasan Pesanan'"></span>
            </h3>
            <div class="space-y-4 mb-4">
                <template x-for="item in cart" :key="item.id">
                    <div class="flex gap-3 items-start">
                        <div class="w-14 h-14 bg-gray-100 rounded-xl overflow-hidden shrink-0 border border-gray-100">
                            <img :src="item.image || 'https://placehold.co/100x100/F3F4F6/9CA3AF?text=Menu'" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-secondary text-sm truncate" x-text="item.name"></h4>
                            <p class="text-[10px] text-primary font-medium mt-0.5 italic" x-show="item.notes">
                                <i data-lucide="info" class="w-3 h-3 inline"></i> <span x-text="item.notes"></span>
                            </p>
                            <div class="flex justify-between items-center mt-1.5">
                                <p class="text-xs font-bold text-gray-400" x-text="item.qty + 'x Rp ' + formatRupiah(item.price)"></p>
                                <p class="font-bold text-secondary text-sm" x-text="'Rp ' + formatRupiah(item.price * item.qty)"></p>
                        </div>
                    </div>
                </template>
            </div>
            
            {{-- Subtotal baru --}}
            <div class="border-t border-dashed border-gray-200 pt-4 space-y-2.5">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 font-medium">
                        Subtotal <span x-show="hasActiveOrder" class="text-[10px] text-primary font-bold">(pesanan ini)</span>
                    </span>
                    <span class="font-bold text-secondary" x-text="'Rp ' + formatRupiah(subtotal())"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 font-medium">Pajak ({{ $setting->tax }}%)</span>
                    <span class="font-bold text-secondary" x-text="'Rp ' + formatRupiah(taxAmount())"></span>
                </div>
                <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                    <span class="font-bold text-secondary" x-text="hasActiveOrder ? 'Tambah Tagihan' : 'Total Tagihan'"></span>
                    <span class="font-black text-primary text-lg" x-text="'Rp ' + formatRupiah(totalAmount())"></span>
                </div>
            </div>

            {{-- Info merge bill --}}
            <div x-show="hasActiveOrder" class="mt-3 bg-amber-50 border border-amber-100 rounded-2xl px-4 py-3 flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4 text-amber-500 flex-shrink-0"></i>
                <p class="text-xs text-amber-700 font-medium">Nominal ini akan ditambahkan ke tagihan sebelumnya</p>
            </div>
        </div>

        <p class="text-xs text-center text-gray-400 font-medium px-4" x-show="!isCheckingOrder">
            Dengan menekan tombol kirim, pesanan akan langsung diproses oleh dapur.
        </p>
    </div>

    {{-- Tombol Submit --}}
    <div x-show="cart.length > 0 && !isCheckingOrder" class="fixed bottom-0 left-0 right-0 max-w-[414px] mx-auto bg-white border-t border-gray-100 p-5 z-40 pb-28 shadow-[0_-10px_20px_rgba(0,0,0,0.02)]">
        <button 
            @click="submitOrder()" 
            :disabled="isSubmitting" 
            :class="isSubmitting ? 'bg-gray-300 cursor-not-allowed' : 'bg-primary hover:bg-[#EA580C] shadow-lg shadow-primary/30 active:scale-95'" 
            class="w-full text-white py-4 rounded-2xl font-bold transition flex items-center justify-center gap-2"
        >
            <template x-if="!isSubmitting">
                <span class="flex items-center gap-2">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span x-text="hasActiveOrder ? 'Tambah ke Pesanan Sekarang' : 'Kirim Pesanan Sekarang'"></span>
                </span>
            </template>
            <template x-if="isSubmitting">
                <span class="flex items-center gap-2">
                    <i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i>
                    <span>Memproses...</span>
                </span>
            </template>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('checkoutApp', () => ({
            cart: [],
            customerName: localStorage.getItem('selfOrderCustomerName') || '',
            customerPhone: localStorage.getItem('selfOrderCustomerPhone') || '',
            orderType: localStorage.getItem('selfOrderType') || 'dine_in',    // dine_in | take_away
            pickupTime: 'Secepatnya',
            takeAwayNotes: '',
            isSubmitting: false,
            isCheckingOrder: true,   // Loading state saat cek order aktif
            hasActiveOrder: false,   // Apakah ada order aktif di meja ini?
            existingOrderNumber: '', // No. order yang sudah ada
            existingItemsCount: 0,   // Jumlah item yang sudah ada di order sebelumnya
            taxRate: {{ $setting->tax }},
            tableId: {{ $table->id ? $table->id : 'null' }},
            tableNumber: "{{ $table->table_number }}",

            initCheckout() {
                this.cart = JSON.parse(localStorage.getItem('selfOrderCart')) || [];
                this.$watch('customerName', val => {
                    localStorage.setItem('selfOrderCustomerName', val);
                    if ((!this.tableId || this.tableId === 'null') && val.trim().length >= 3) {
                        this.checkActiveOrder();
                    }
                });
                this.$watch('customerPhone', val => localStorage.setItem('selfOrderCustomerPhone', val));
                this.$watch('orderType', val => localStorage.setItem('selfOrderType', val));
                if (this.cart.length > 0) {
                    this.checkActiveOrder();
                } else {
                    this.isCheckingOrder = false;
                }
                setTimeout(() => { lucide.createIcons(); }, 100);
            },

            /**
             * Cek ke server: apakah meja ini punya order aktif yang belum dibayar?
             * Jika ada, auto-fill nama & HP dari order sebelumnya.
             */
            checkActiveOrder() {
                this.isCheckingOrder = true;
                $.get("{{ route('customer.checkActiveOrder') }}", { table_id: this.tableId, customer_name: this.customerName }, (res) => {
                    if (res.has_active_order) {
                        this.hasActiveOrder    = true;
                        this.customerName      = res.customer_name;
                        this.customerPhone     = res.phone || '';
                        this.existingOrderNumber = res.order_number;
                        this.existingItemsCount  = res.items_count;
                    } else {
                        this.hasActiveOrder = false;
                    }
                    this.isCheckingOrder = false;
                    this.$nextTick(() => { lucide.createIcons(); });
                }).fail(() => {
                    // Jika gagal check, anggap tidak ada order aktif (safe fallback)
                    this.hasActiveOrder  = false;
                    this.isCheckingOrder = false;
                });
            },

            subtotal()    { return this.cart.reduce((sum, i) => sum + (i.price * i.qty), 0); },
            taxAmount()   { return this.subtotal() * (this.taxRate / 100); },
            totalAmount() { return this.subtotal() + this.taxAmount(); },
            formatRupiah(num) { return new Intl.NumberFormat('id-ID').format(num); },

            submitOrder() {
                // Validasi nama jika BUKAN returning customer ATAU jika pilih Take Away
                if ((!this.hasActiveOrder || this.orderType === 'take_away') && !this.customerName.trim()) { 
                    Swal.fire({ 
                        icon: 'warning', 
                        title: 'Oops...', 
                        text: 'Mohon isi Nama Lengkap Anda terlebih dahulu.', 
                        confirmButtonColor: '#F97316', 
                        customClass: { popup: 'rounded-3xl' } 
                    });
                    return; 
                }
                if (this.cart.length === 0) return;

                this.isSubmitting = true;
                
                $.ajax({
                    url: "{{ route('customer.storeOrder') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        table_id: this.tableId,
                        table_number: this.tableNumber,
                        customer_name: this.customerName,
                        phone: this.customerPhone,
                        order_type: this.orderType,
                        pickup_time: null,
                        take_away_notes: this.takeAwayNotes,
                        items: this.cart.map(i => ({
                            id: i.id,
                            qty: i.qty,
                            price: i.price,
                            notes: i.notes || '',
                            is_take_away: 0
                        }))
                    },
                    success: (res) => {
                        localStorage.removeItem('selfOrderCart');
                        window.location.href = res.redirect_url;
                    },
                    error: (xhr) => {
                        Swal.fire({ 
                            icon: 'error', 
                            title: 'Gagal', 
                            text: xhr.responseJSON?.message || 'Gagal mengirim pesanan ke dapur.', 
                            confirmButtonColor: '#F97316', 
                            customClass: { popup: 'rounded-3xl' } 
                        });
                        this.isSubmitting = false;
                    }
                });
            }
        }));
    });
</script>
@endpush