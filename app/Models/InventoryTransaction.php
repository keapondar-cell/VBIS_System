<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'user_id', 'department_id', 'transaction_type', 'quantity', 'quantity_change',
        'old_quantity', 'new_quantity', 'notes', 'rejection_reason', 'related_transaction_id', 'expected_return_at',
        'returned_at', 'status', 'condition', 'performed_by', 'affected_user_id', 'reason',
        'remarks', 'reference_number', 'returned_by'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function relatedTransaction()
    {
        return $this->belongsTo(self::class, 'related_transaction_id');
    }

    public function returnTransaction()
    {
        return $this->hasOne(self::class, 'related_transaction_id');
    }

    protected function casts(): array
    {
        return [
            'expected_return_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }
}
