<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PricingFactor extends Model
{
    use HasFactory;

    protected $table = 'pricing_factors';

    protected $fillable = [
        'type',           // height | condition | complexity | region
        'code',           // rdc | r1_r3 | r4_plus | bon | moyen | mauvais | ...
        'name',           // "RDC (≤ 3m)" | "Bon état"
        'description',    // shown in tooltip/select
        'multiplier',     // 1.00 | 1.15 | 1.35 | ...
        'is_default',     // pre-selected option for this type
        'display_order',
    ];

    protected $casts = [
        'multiplier'    => 'decimal:2',
        'is_default'    => 'boolean',
        'display_order' => 'integer',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Filter by factor type */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type)->orderBy('display_order');
    }

    /** Only default factors (one per type) */
    public function scopeDefaults($query)
    {
        return $query->where('is_default', true);
    }

    /** Ordered within type */
    public function scopeOrdered($query)
    {
        return $query->orderBy('type')->orderBy('display_order');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Returns all factors grouped by type — used by ServiceController::pricingFactors()
     * Result shape: { height: [...], condition: [...], complexity: [...], region: [...] }
     */
    public static function grouped(): array
    {
        return self::ordered()
            ->get()
            ->groupBy('type')
            ->map(fn($group) => $group->values())
            ->toArray();
    }

    /**
     * Get the default multiplier for a given type (fallback to 1.00)
     */
    public static function defaultMultiplier(string $type): float
    {
        return (float) (self::where('type', $type)->where('is_default', true)->value('multiplier') ?? 1.00);
    }
}