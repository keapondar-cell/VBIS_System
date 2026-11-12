<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'description', 'category', 'quantity', 'location', 'status', 'qr_code', 'usage_type'
    ];

    protected $appends = ['classification'];

    protected static function booted(): void
    {
        static::creating(function (Item $item): void {
            $item->qr_code ??= 'VBIS-'.Str::upper(Str::random(16));
            $item->usage_type ??= $item->isConsumable() ? 'issuance_only' : 'both';
        });
    }

    public function getClassificationAttribute(): ?string
    {
        return $this->attributes['category'] ?? null;
    }

    public function setClassificationAttribute($value): void
    {
        $this->attributes['category'] = $value;
    }

    public function isConsumable(): bool
    {
        return strtolower(trim((string) $this->category)) === 'consumable';
    }

    public function allowsMaterialIssuance(): bool
    {
        return in_array($this->usage_type, ['issuance_only', 'both'], true);
    }

    public function allowsBorrowing(): bool
    {
        return in_array($this->usage_type, ['borrowing_only', 'both'], true);
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
