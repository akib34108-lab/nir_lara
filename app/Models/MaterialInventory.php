<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialInventory extends Model
{
    protected $fillable = ['warehouse_id', 'material_id', 'quantity', 'reserved_quantity', 'average_cost'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'reserved_quantity' => 'decimal:3', 'average_cost' => 'decimal:2'];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
