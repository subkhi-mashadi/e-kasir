<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'code', 'phone', 'address', 'qris_image', 'qris_static_string', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
