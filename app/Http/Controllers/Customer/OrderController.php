<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Table;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    private function resolveTable($tableNumber)
    {
        $table = Table::where('table_number', $tableNumber)->first();
        if (!$table && in_array(strtolower(trim($tableNumber ?? '')), ['counter / bungkus', 'bungkus', 'take_away', 'counter', 'take away', 'takeaway'])) {
            $table = new Table([
                'table_number' => 'Counter / Bungkus',
                'capacity' => 0,
                'status' => 'available'
            ]);
        }
        return $table;
    }

    public function welcome(Request $request)
    {
        $tableNumber = $request->query('table');
        $table = $this->resolveTable($tableNumber);

        // JIKA MEJA TIDAK DITEMUKAN
        if (!$table) {
            $setting = Setting::first();
            return view('customer.invalid-table', compact('setting'));
        }

        // Ambil profil restoran jika meja valid
        $setting = Setting::first();

        // Ambil Menu Bestseller (Top 5 Menu Paling Banyak Dipesan)
        // Menggunakan pluck ID agar terhindar dari error ONLY_FULL_GROUP_BY bawaan MySQL
        $topMenuIds = DB::table('order_items')
            ->select('menu_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('menu_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->pluck('menu_id')
            ->toArray();

        $bestSellers = collect();
        if (!empty($topMenuIds)) {
            // Ambil data menu dan pertahankan urutannya sesuai dengan urutan penjualan terbanyak
            $bestSellers = Menu::whereIn('id', $topMenuIds)
                ->where('is_available', true)
                ->get()
                ->sortBy(function($model) use ($topMenuIds) {
                    return array_search($model->id, $topMenuIds);
                })
                ->values();
        }

        // Jika belum ada penjualan, ambil menu secara acak
        if ($bestSellers->isEmpty()) {
            $bestSellers = Menu::where('is_available', true)->inRandomOrder()->limit(5)->get();
        }

        return view('customer.welcome', compact('table', 'setting', 'bestSellers'));
    }

    public function menu(Request $request)
    {
        $tableNumber = $request->query('table');
        $table = $this->resolveTable($tableNumber);

        if (!$table) {
            $setting = Setting::first();
            return view('customer.invalid-table', compact('setting'));
        }

        // Ambil Data Kategori & Menu yang Aktif
        $categories = Category::where('is_active', true)->get();
        $menus = Menu::with('category')->where('is_available', true)->get();
        $setting = Setting::first();

        return view('customer.menu', compact('table', 'categories', 'menus', 'setting'));
    }

    public function checkout(Request $request)
    {
        $tableNumber = $request->query('table');
        $table = $this->resolveTable($tableNumber);
        if (!$table) {
            $setting = Setting::first();
            return view('customer.invalid-table', compact('setting'));
        }
        $setting = Setting::first();

        return view('customer.checkout', compact('table', 'setting'));
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'order_type' => 'required|in:dine_in,take_away',
            'table_id' => 'nullable|exists:tables,id',
            'customer_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'pickup_time' => 'nullable|string|max:100',
            'take_away_notes' => 'nullable|string|max:500',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menus,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.is_take_away' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $setting = Setting::first();
            
            // Tetap catat table_id jika memesan dari meja fisik (meskipun tipe pesanan Take Away)
            $tableId = (!empty($request->table_id) && $request->table_id !== 'null') ? $request->table_id : null;
            
            // 1. CEK TRANSAKSI AKTIF (MERGE ORDER)
            $activeOrder = null;
            if ($tableId && $tableId !== 'null') {
                // Jika di meja fisik yang sama, satukan tagihan
                $activeOrder = Order::where('table_id', $tableId)
                                    ->where('payment_status', 'unpaid')
                                    ->first();
            } elseif (!empty($request->customer_name)) {
                // Jika pesanan Counter / Take Away (tanpa meja fisik), satukan tagihan berdasarkan nama pelanggan yang sama hari ini
                $activeOrder = Order::whereNull('table_id')
                                    ->where('customer_name', trim($request->customer_name))
                                    ->where('payment_status', 'unpaid')
                                    ->whereDate('created_at', today())
                                    ->first();
            }

            if ($activeOrder) {
                // JIKA ADA: Gunakan Order ID yang sudah ada
                $order = $activeOrder;
                $order->status = 'pending';
                $order->save();
            } else {
                // JIKA TIDAK ADA: Buat Order Baru
                $todayOrders = Order::whereDate('created_at', today())->count() + 1;
                $orderNumber = 'ORD-' . date('dmy') . '-' . str_pad($todayOrders, 3, '0', STR_PAD_LEFT);

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'table_id' => $tableId,
                    'customer_name' => $request->customer_name,
                    'phone' => $request->phone,
                    'order_type' => $request->order_type,
                    'pickup_time' => $request->pickup_time,
                    'take_away_notes' => $request->take_away_notes,
                    'total_amount' => 0,
                    'status' => 'pending',
                    'payment_status' => 'unpaid'
                ]);

                if ($tableId) {
                    Table::where('id', $tableId)->update(['status' => 'occupied']);
                }
            }

            // 2. MASUKKAN ITEM BARU KE DATABASE
            foreach($request->items as $item) {
                $isTakeAwayItem = (isset($item['is_take_away']) && ($item['is_take_away'] === true || $item['is_take_away'] === 'true' || $item['is_take_away'] === 1 || $item['is_take_away'] === '1' || $item['is_take_away'] === 'on' || $item['is_take_away'] === 'yes')) || $request->order_type === 'take_away';
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                    'notes' => $item['notes'] ?? null,
                    'is_take_away' => $isTakeAwayItem
                ]);
            }

            // 3. HITUNG ULANG TOTAL TAGIHAN
            $subtotal = OrderItem::where('order_id', $order->id)->sum('subtotal');
            $tax = $subtotal * (($setting->tax ?? 0) / 100);
            $total = $subtotal + $tax;

            // 4. UPDATE TOTAL AMOUNT
            $order->update(['total_amount' => $total]);

            // Tampilan nomor meja untuk redirect (Null-Safe)
            $tableObj = $tableId ? Table::find($tableId) : null;
            $tableDisplay = $tableObj ? $tableObj->table_number : ($request->table_number ?: 'Counter / Bungkus');

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect_url' => route('customer.success', ['orderNumber' => $order->order_number, 'table' => $tableDisplay])
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('StoreOrder Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false, 
                'message' => 'Gagal: ' . $e->getMessage() . ' (Line ' . $e->getLine() . ')'
            ], 500);
        }
    }

    public function success(Request $request, $orderNumber)
    {
        $tableNumber = $request->query('table');
        $order = Order::with(['items.menu'])->where('order_number', $orderNumber)->firstOrFail();
        $setting = Setting::first();

        return view('customer.success', compact('order', 'tableNumber', 'setting'));
    }

    public function status(Request $request, $orderNumber)
    {
        $tableNumber = $request->query('table');
        $order = Order::with(['items.menu'])->where('order_number', $orderNumber)->firstOrFail();
        $setting = Setting::first();

        return view('customer.status', compact('order', 'tableNumber', 'setting'));
    }

    public function checkStatus($orderNumber)
    {
        // Method ringan untuk di-hit oleh Alpine.js setiap beberapa detik
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        
        return response()->json([
            'status' => $order->status,
            'time' => $order->updated_at->format('H:i')
        ]);
    }
    
    public function ready()
    {
        $orders = Order::with(['table', 'items.menu'])
            ->where('status', 'ready')
            ->oldest()
            ->get();

        return view('kitchen.dashboard.ready', compact('orders'));
    }

    /**
     * Cek apakah meja sudah punya order aktif (unpaid).
     * Jika ada, kembalikan data customer agar tidak perlu input ulang.
     */
    public function checkActiveOrder(Request $request)
    {
        $tableId = $request->query('table_id');
        $customerName = $request->query('customer_name');

        $activeOrder = null;
        if ($tableId && $tableId !== 'null') {
            $activeOrder = Order::where('table_id', $tableId)
                                ->where('payment_status', 'unpaid')
                                ->latest()
                                ->first();
        } elseif (!empty($customerName)) {
            $activeOrder = Order::whereNull('table_id')
                                ->where('customer_name', trim($customerName))
                                ->where('payment_status', 'unpaid')
                                ->whereDate('created_at', today())
                                ->latest()
                                ->first();
        }

        if ($activeOrder) {
            return response()->json([
                'has_active_order' => true,
                'order_number'     => $activeOrder->order_number,
                'customer_name'    => $activeOrder->customer_name,
                'phone'            => $activeOrder->phone ?? '',
                'items_count'      => $activeOrder->items()->count(),
            ]);
        }

        return response()->json(['has_active_order' => false]);
    }
}