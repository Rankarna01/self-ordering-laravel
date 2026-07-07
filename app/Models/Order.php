<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 
        'table_id', 
        'customer_name', 
        'phone',
        'total_amount', 
        'order_type',
        'pickup_time',
        'take_away_notes',
        'status', 
        'payment_status'
    ];

    // 1. Relasi ke Meja dengan default value jika table_id NULL (Take Away murni)
    public function table()
    {
        return $this->belongsTo(Table::class)->withDefault([
            'table_number' => 'Counter',
            'status' => 'occupied'
        ]);
    }

    // Accessor untuk tampilan nomor meja / bungkus yang rapi
    public function getTableNumberDisplayAttribute()
    {
        $tableRelation = $this->getRelationValue('table');
        if ($this->order_type === 'take_away') {
            if ($this->table_id && $tableRelation && $tableRelation->table_number !== 'Counter') {
                return 'Take Away / Bungkus (Meja ' . $tableRelation->table_number . ')';
            }
            return 'Take Away / Bungkus';
        }
        return ($this->table_id && $tableRelation && $tableRelation->table_number !== 'Counter') ? 'Meja ' . $tableRelation->table_number : 'Counter';
    }

    // 2. Relasi ke Detail Pesanan
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // 3. Relasi ke Pembayaran (Untuk fitur Kasir)
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}