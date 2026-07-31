<?php

namespace Webkul\Tenant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\Tenant\Contracts\Tenant as TenantContract;
use Webkul\User\Models\Admin;

class Tenant extends Model implements TenantContract
{
    use SoftDeletes;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'business_type',
        'theme',
        'template',
        'locale',
        'mobile',
        'address',
        'modules',
        'settings',
        'customer_panel_features',
        'is_active',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected $casts = [
        'modules' => 'array',
        'settings' => 'array',
        'customer_panel_features' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function users()
    {
        return $this->belongsToMany(
            Admin::class,
            'tenant_user',
            'tenant_id',
            'user_id'
        )->withPivot('role')->withTimestamps();
    }
}
