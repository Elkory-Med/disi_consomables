<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    protected $table = 'order_history';

    protected $fillable = [
        'order_id',
        'status',
        'notes',
        'changed_by'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
