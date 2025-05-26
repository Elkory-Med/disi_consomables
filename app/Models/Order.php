<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'admin_notes',
        'approved_at',
        'approved_by',
        'quantity',
        'delivered',
        'delivered_at',
        'rejected_at',
        'administration',
        'unite'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'delivered_at' => 'datetime',
        'rejected_at' => 'datetime',
        'delivered' => 'boolean'
    ];

    // Define possible status values
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public static function getAllowedStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_DELIVERED
        ];
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_APPROVED => 'Approuvée',
            self::STATUS_REJECTED => 'Rejetée',
            self::STATUS_DELIVERED => 'Livrée'
        ];
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItems::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Add relationship for the admin who approved/rejected the order
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Add relationship for order history
    public function history()
    {
        return $this->hasMany(OrderHistory::class)->orderBy('created_at', 'desc');
    }

    // Helper method to add history entry
    public function addHistoryEntry($status, $notes, $changedBy)
    {
        return $this->history()->create([
            'status' => $status,
            'notes' => $notes,
            'changed_by' => $changedBy
        ]);
    }

    // Helper methods to check status
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isDelivered()
    {
        return $this->delivered;
    }

    public function order_items()
    {
        return $this->hasMany(OrderItems::class);
    }

    public function markAsDelivered()
    {
        $this->update([
            'delivered' => true,
            'delivered_at' => now()
        ]);
    }

    // Query scopes for dashboard
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('delivered', true);
    }

    public function scopeUndelivered($query)
    {
        return $query->where('delivered', false);
    }

    public function scopeLastWeek($query)
    {
        return $query->whereBetween('created_at', [now()->subWeek(), now()]);
    }

    // Accessors for dashboard
    public function getStatusLabelAttribute()
    {
        return self::getStatuses()[$this->status] ?? 'Inconnu';
    }

    public function getDeliveryStatusAttribute()
    {
        if ($this->delivered) {
            return sprintf('Livrée le %s', $this->delivered_at ? $this->delivered_at->format('d/m/Y H:i') : 'N/A');
        }
        return 'Non livrée';
    }

    public function getTotalProductsAttribute()
    {
        return $this->orderItems()->count();
    }

    // Add getters for formatted dates
    public function getFormattedDeliveredAtAttribute()
    {
        return $this->delivered_at ? $this->delivered_at->format('d/m/Y H:i') : 'N/A';
    }

    public function getFormattedRejectedAtAttribute()
    {
        return $this->rejected_at ? $this->rejected_at->format('d/m/Y H:i') : 'N/A';
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : 'N/A';
    }

    // Event hooks
    protected static function booted()
    {
        static::updating(function ($order) {
            if ($order->isDirty('delivered') && $order->delivered) {
                $order->delivered_at = now();
            }
            if ($order->isDirty('status') && $order->status === self::STATUS_REJECTED) {
                $order->rejected_at = now();
            }
        });
    }
}
