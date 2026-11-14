<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use softDeletes;
    protected $fillable = [
    'client_code',
    'client_type',
    'name',
    'company_name',
    'email',
    'phone',
    'alternate_phone',
    'website',
    'tax_number',
    'address',
    'city',
    'state',
    'country',
    'postal_code',
    'notes',
    'status',
];
}
