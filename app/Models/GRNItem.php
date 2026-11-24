<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GRNItem extends Model
{
    protected $table = 'grn_items';

    protected $fillable = ['goods_received_note_id', 'purchase_order_item_id', 'material_id', 'ordered_quantity', 'received_quantity', 'accepted_quantity', 'rejected_quantity'];

    protected function casts(): array
    {
        return ['ordered_quantity' => 'decimal:3', 'received_quantity' => 'decimal:3', 'accepted_quantity' => 'decimal:3', 'rejected_quantity' => 'decimal:3'];
    }

    public function grn()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'goods_received_note_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function inspections()
    {
        return $this->hasMany(MaterialInspection::class, 'grn_item_id');
    }
}
