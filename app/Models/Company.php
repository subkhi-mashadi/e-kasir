<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Company extends Model
{
    protected static function booted(): void
    {
        static::created(function (Company $company) {
            $plan = \App\Models\Plan::where('slug', 'starter')->where('is_active', true)->first();
            if ($plan) {
                (new \App\Services\SubscriptionService())->createTrial($company, $plan);
            }
        });
    }

    protected $fillable = [
        'name', 'slug', 'phone', 'address', 'logo',
        'currency', 'timezone', 'tax_rate', 'tax_inclusive',
        'receipt_settings', 'is_active',
        'midtrans_server_key', 'midtrans_client_key', 'midtrans_is_production',
        'xendit_secret_key', 'payment_provider', 'qris_static_image', 'qris_static_string',
    ];

    protected $casts = [
        'tax_rate'                => 'decimal:2',
        'tax_inclusive'           => 'boolean',
        'receipt_settings'        => 'array',
        'is_active'               => 'boolean',
        'midtrans_is_production'  => 'boolean',
    ];

    protected $hidden = [
        'midtrans_server_key',
        'midtrans_client_key',
        'xendit_secret_key',
    ];

    public function getMidtransServerKeyAttribute($value): ?string
    {
        if (! $value) return null;
        try { return decrypt($value); } catch (\Exception $e) { return null; }
    }

    public function setMidtransServerKeyAttribute(?string $value): void
    {
        $this->attributes['midtrans_server_key'] = $value ? encrypt($value) : null;
    }

    public function getMidtransClientKeyAttribute($value): ?string
    {
        if (! $value) return null;
        try { return decrypt($value); } catch (\Exception $e) { return null; }
    }

    public function setMidtransClientKeyAttribute(?string $value): void
    {
        $this->attributes['midtrans_client_key'] = $value ? encrypt($value) : null;
    }

    public function getXenditSecretKeyAttribute($value): ?string
    {
        if (! $value) return null;
        try { return decrypt($value); } catch (\Exception $e) { return null; }
    }

    public function setXenditSecretKeyAttribute(?string $value): void
    {
        $this->attributes['xendit_secret_key'] = $value ? encrypt($value) : null;
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function plan(): ?Plan
    {
        return $this->subscription?->plan;
    }

    public function canAddBranch(): bool
    {
        $max = $this->plan()?->max_branches ?? 1;
        return $this->branches()->count() < $max;
    }

    public function canAddUser(): bool
    {
        $max = $this->plan()?->max_users ?? 3;
        return $this->users()->count() < $max;
    }

    public function canAddProduct(): bool
    {
        $max = $this->plan()?->max_products ?? 50;
        return Product::where('company_id', $this->id)->count() < $max;
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) ($this->plan()?->$feature ?? false);
    }
}
