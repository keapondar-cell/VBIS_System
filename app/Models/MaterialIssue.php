<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'issued_to_user_id', 'department_id', 'requested_for_at', 'expected_return_at', 'quantity', 'status', 'approved_by', 'approved_at', 'notes', 'rejection_reason'
    ];

    protected function casts(): array
    {
        return [
            'requested_for_at' => 'datetime',
            'expected_return_at' => 'datetime',
        ];
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function issuedTo()
    {
        return $this->belongsTo(\App\Models\User::class, 'issued_to_user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
