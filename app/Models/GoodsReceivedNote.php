<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceivedNote extends Model
{
    use SoftDeletes;

    protected $fillable = ['grn_number', 'purchase_order_id', 'warehouse_id', 'received_by', 'received_date', 'notes', 'status', 'confirmed_at'];

    protected function casts(): array
    {
        return ['received_date' => 'date', 'confirmed_at' => 'datetime'];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(GRNItem::class);
    }

    public function inspections()
    {
        return $this->hasMany(MaterialInspection::class);
    }
}
