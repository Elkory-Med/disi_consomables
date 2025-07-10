<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description', 
        'category_id', 
        'image'   
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeSearch($query, $value){
        $query->where('name','like',"%{$value}%")
        ->orWhere('description','like',"%{$value}%");
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItems::class);
    }
    
    public function hasRestrictedOrders()
    {
        return $this->orderItems()
            ->whereHas('order', function($query) {
                $query->where('status', 'approved')
                      ->orWhere('status', 'pending')
                      ->orWhere('status', 'rejected')
                      ->orWhere('delivered', true);
            })
            ->exists();
    }

    public function getOrderStatusesAttribute()
    {
        $orderStatuses = $this->orderItems()
            ->whereHas('order', function($query) {
                $query->where('status', 'approved')
                      ->orWhere('status', 'pending')
                      ->orWhere('status', 'rejected')
                      ->orWhere('delivered', true);
            })
            ->with('order')
            ->get()
            ->pluck('order')
            ->unique()
            ->map(function($order) {
                if ($order->delivered) {
                    return 'livré';
                }
                return match($order->status) {
                    'approved' => 'approuvé',
                    'pending' => 'en attente',
                    'rejected' => 'rejeté',
                    default => $order->status
                };
            });
            
        // Count occurrences of each status
        $statusCounts = $orderStatuses->countBy();
        
        // Format as "status (count)" or just "status" if count is 1
        $formattedStatuses = $statusCounts->map(function($count, $status) {
            return $count > 1 ? "$status ($count)" : $status;
        })->values()->implode(', ');

        return $formattedStatuses;
    }
    public function shoppingCarts()
    {
        return $this->hasMany(ShoppingCart::class);
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($product) {
            // Sanitize product name
            if (isset($product->name) && !mb_check_encoding($product->name, 'UTF-8')) {
                Log::error('Invalid UTF-8 detected in product name:', ['value' => $product->name]);
                $product->name = 'Invalid Data';
            }

            // Sanitize product description
            if (isset($product->description) && !mb_check_encoding($product->description, 'UTF-8')) {
                Log::error('Invalid UTF-8 detected in product description:', ['value' => $product->description]);
                $product->description = 'Invalid Data';
            }
        });
    }
}
