<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'material_id',
        'requested_quantity',
        'approved_quantity',
        'required_date',
    ];

    protected function casts(): array
    {
        return [
            'requested_quantity' => 'decimal:3',
            'approved_quantity' => 'decimal:3',
            'required_date' => 'date',
        ];
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_request_item_id');
    }

    public function purchaseOrdersRelationQuery()
    {
        return $this->purchaseOrderItems();
    }
}
