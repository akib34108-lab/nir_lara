<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialInspection extends Model
{
    protected $fillable = ['goods_received_note_id', 'grn_item_id', 'inspector_id', 'inspected_quantity', 'accepted_quantity', 'rejected_quantity', 'inspection_date', 'quality_status', 'remarks'];

    protected function casts(): array
    {
        return ['inspected_quantity' => 'decimal:3', 'accepted_quantity' => 'decimal:3', 'rejected_quantity' => 'decimal:3', 'inspection_date' => 'date'];
    }

    public function grn()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'goods_received_note_id');
    }

    public function grnItem()
    {
        return $this->belongsTo(GRNItem::class, 'grn_item_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
