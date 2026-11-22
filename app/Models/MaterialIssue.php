<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialIssue extends Model
{
    use SoftDeletes;

    protected $fillable = ['issue_number', 'project_id', 'site', 'warehouse_id', 'requested_by', 'issued_by', 'approved_by', 'issue_date', 'status', 'notes', 'confirmed_at'];

    protected function casts(): array
    {
        return ['issue_date' => 'date', 'confirmed_at' => 'datetime'];
    }

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(MaterialIssueItem::class);
    }
}
