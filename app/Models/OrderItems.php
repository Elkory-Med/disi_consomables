<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'status', // Added status field
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Define the relationship with Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
