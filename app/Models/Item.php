<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'description', 'category', 'quantity', 'location', 'status', 'qr_code'
    ];

    protected static function booted(): void
    {
        static::creating(function (Item $item): void {
            $item->qr_code ??= 'VBIS-'.Str::upper(Str::random(16));
        });
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function issues()
    {
        return $this->hasMany(MaterialIssue::class);
    }
}
