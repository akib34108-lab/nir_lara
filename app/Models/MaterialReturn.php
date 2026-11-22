<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialReturn extends Model
{
    use SoftDeletes;

    protected $fillable = ['return_number', 'return_type', 'project_id', 'site', 'warehouse_id', 'supplier_id', 'responsible_user_id', 'approved_by', 'return_date', 'reason', 'status', 'approved_at'];

    protected function casts(): array
    {
        return ['return_date' => 'date', 'approved_at' => 'datetime'];
    }

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(MaterialReturnItem::class);
    }
}
