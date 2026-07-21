<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->useLogName('product')->dontSubmitEmptyLogs();
    }
    protected $guarded = [];

    protected $casts = [
        'images'          => 'array',
        'variants'        => 'array',
        'tags'            => 'array',
        'allow_oversell'  => 'boolean',
        'track_quantity'  => 'boolean',
        'requires_shipping' => 'boolean',
        'charge_taxes'    => 'boolean',
        'status'          => 'boolean',
        'ebook_price'     => 'decimal:2',
        'hardcopy_price'  => 'decimal:2',
        'pages'           => 'integer',
        'pdf_page_count'  => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProductSection::class, 'section_id');
    }

    public function pdfPages(): HasMany
    {
        return $this->hasMany(ProductPage::class)->orderBy('page_number');
    }
}
