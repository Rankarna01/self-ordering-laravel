<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'menu_id', 'quantity', 'price', 'subtotal', 'notes', 'is_take_away'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}