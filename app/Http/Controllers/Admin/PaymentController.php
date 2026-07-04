<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        // Ambil order yang belum dibayar (urut dari yang paling lama menunggu)
        $unpaidOrders = Order::with(['table', 'items.menu'])
            ->where('payment_status', 'unpaid')
            ->oldest()
            ->get();

        // Ambil history order yang sudah dibayar (terbaru)
        $paidOrders = Order::with(['table', 'payment'])
            ->where('payment_status', 'paid')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.payments.index', compact('unpaidOrders', 'paidOrders'));
    }

    public function detail(Order $order)
    {
        $order->load(['table', 'items.menu']);
        return response()->json($order);
    }

    public function process(Request $request, Order $order)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,qris,transfer',
            'amount_paid' => 'required|numeric|min:' . $order->total_amount,
        ]);

        if ($order->payment_status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Pesanan ini sudah dibayar sebelumnya.'], 400);
        }

        DB::beginTransaction();
        try {
            // Hitung kembalian
            $change = $request->amount_paid - $order->total_amount;
            
            // Jika QRIS/Transfer, uang pas
            if (in_array($request->payment_method, ['qris', 'transfer'])) {
                $change = 0;
                $request->merge(['amount_paid' => $order->total_amount]);
            }

            // Simpan Payment
            Payment::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'payment_method' => $request->payment_method,
                'amount_paid' => $request->amount_paid,
                'change' => $change
            ]);

            // Update Order Status
            $order->update([
                'payment_status' => 'paid',
                'status' => 'completed' // Otomatis selesai jika sudah dibayar (opsional, sesuaikan alur bisnis)
            ]);

            // Ubah status meja menjadi available lagi jika pesanan menggunakan meja (Dine-In)
            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'available']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses!',
                'print_url' => route('admin.payments.receipt', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }

    public function receipt(Order $order)
    {
        $order->load(['table', 'items.menu', 'payment.user']);
        
        if ($order->payment_status !== 'paid') {
            abort(404, 'Struk belum tersedia karena belum dibayar.');
        }

        // View khusus untuk thermal printer
        return view('admin.payments.receipt', compact('order'));
    }
}