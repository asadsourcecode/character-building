<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductSection extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->useLogName('product_section')->dontSubmitEmptyLogs();
    }
    protected $guarded = [];

    protected $casts = [
        'price'        => 'decimal:2',
        'sale_price'   => 'decimal:2',
        'is_available' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'section_id')->orderBy('id');
    }
}
