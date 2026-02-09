<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'option_name',
        'option_type',
        'multiplier',
        'is_default',
        'description',
    ];

    protected $casts = [
        'multiplier' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Scopes
    public function scopeDefaults($query)
    {
        return $query->where('is_default', true);
    }
}