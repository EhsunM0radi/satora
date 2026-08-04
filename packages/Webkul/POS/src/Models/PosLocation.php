<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosLocation as PosLocationContract;
use Webkul\POS\Contracts\PosLocationModel;
use Webkul\POS\Support\BelongsToTenant;

class PosLocation extends Model implements PosLocationContract, PosLocationModel
{
    use BelongsToTenant;

    protected $table = 'pos_locations';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'timezone',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function terminals()
    {
        return $this->hasMany(PosTerminal::class, 'pos_location_id');
    }
}
