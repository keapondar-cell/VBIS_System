<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemsExport implements FromCollection, WithHeadings
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return $this->items->map(function ($i) {
            return [
                'id' => $i->id,
                'sku' => $i->sku,
                'name' => $i->name,
                'category' => $i->category,
                'location' => $i->location,
                'quantity' => $i->quantity,
                'status' => $i->status,
                'qr_code' => $i->qr_code,
                'created_at' => $i->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return ['id','sku','name','category','location','quantity','status','qr_code','created_at'];
    }
}
