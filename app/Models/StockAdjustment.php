<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = ['adjustment_number', 'warehouse_id', 'material_id', 'previous_quantity', 'adjusted_quantity', 'difference', 'reason', 'remarks', 'created_by', 'approved_by', 'status', 'approved_at'];

    protected function casts(): array
    {
        return ['previous_quantity' => 'decimal:3', 'adjusted_quantity' => 'decimal:3', 'difference' => 'decimal:3', 'approved_at' => 'datetime'];
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
