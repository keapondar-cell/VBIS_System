<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function materialIssues()
    {
        return $this->hasMany(MaterialIssue::class);
    }
}
