<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'requested_by', 'old_quantity', 'new_quantity', 'quantity_change', 'delta',
        'reason', 'notes', 'status', 'approved_by', 'approved_at', 'performed_by', 'approved_quantity'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function requester()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
