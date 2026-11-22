<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = ['warehouse_id', 'material_id', 'transaction_type', 'quantity', 'balance_quantity', 'reference_type', 'reference_id', 'created_by', 'notes'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'balance_quantity' => 'decimal:3'];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
