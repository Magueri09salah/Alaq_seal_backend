<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToitureDevis extends Model
{
    use HasFactory;

    protected $table = 'toiture_devis';

    protected $fillable = [
        'user_id',
        'devis_number',
        'status',
        'project_name',
        'project_location',
        'notes',
        'type',
        'toiture_type',
        'isolation',
        'finition',
        'longueur',
        'largeur',
        'perimetre',
        'hauteur_acrotere',
        'hauteur',
        'nombre_murs',
        'nombre_evacuations',
        'chape_existante',
        'surface_brute',
        'surface_technique',
        'surface_releves',
        'total_ht',
        'tva_rate',
        'tva_amount',
        'total_ttc',
        'materials',
        'submitted_at',
        'salle_bain_data',
        'water_level',      // ADD THIS
        'drain',            // ADD THIS
        'hauteur_technique', // ADD THIS
        'sdb_type',         // ADD THIS
        'support', 
        'drain',
        'pdf_token',
    ];

    protected $casts = [
        'materials' => 'array',
        'isolation' => 'boolean',
        'chape_existante' => 'boolean',
        'longueur' => 'decimal:2',
        'largeur' => 'decimal:2',
        'perimetre' => 'decimal:2',
        'hauteur_acrotere' => 'decimal:2',
        'hauteur' => 'decimal:2',
        'surface_brute' => 'decimal:2',
        'surface_technique' => 'decimal:2',
        'surface_releves' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'tva_rate' => 'decimal:2',
        'tva_amount' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'salle_bain_data' => 'array',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // ── Methods ──────────────────────────────────────────────────────────────

    /**
     * Generate unique devis number
     */
    public static function generateNumber($userId)
    {
        $year = now()->format('Y');
        $prefix = "DEVIS-{$userId}-{$year}-";
        
        return \DB::transaction(function () use ($userId, $year, $prefix) {
            $lastDevis = self::where('user_id', $userId)
                ->where('devis_number', 'like', "{$prefix}%")
                ->lockForUpdate()  // ← KEY: Locks the row
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastDevis) {
                $lastNumber = (int) substr($lastDevis->devis_number, strrpos($lastDevis->devis_number, '-') + 1);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Check if devis can be edited
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'saved']);
    }

    /**
     * Check if devis can be deleted
     */
    public function canDelete(): bool
    {
        return in_array($this->status, ['draft', 'saved']);
    }

    /**
     * Submit devis
     */
    public function submit(): void
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'toiture' => 'Étanchéité toiture plate',
            'mur' => 'Étanchéité mur enterré',
            'salle_bain' => 'Étanchéité sous carrelage',
            default => $this->type,
        };
    }

    /**
     * Get toiture type label
     */
    public function getToitureTypeLabelAttribute(): ?string
    {
        if ($this->type !== 'toiture') return null;
        
        return match($this->toiture_type) {
            'accessible' => 'Accessible',
            'non_accessible' => 'Non accessible',
            default => null,
        };
    }

    /**
     * Get finition label
     */
    public function getFinitionLabelAttribute(): ?string
    {
        if (!$this->finition) return null;
        
        return match($this->finition) {
            'autoprotegee' => 'Autoprotégée ardoisée',
            'lestage' => 'Finition lisse + Lestage',
            default => null,
        };
    }

        public function ensurePdfToken(): string
    {
        if (!$this->pdf_token) {
            $this->pdf_token = bin2hex(random_bytes(32));
            $this->save();
        }
        return $this->pdf_token;
    }

        public function getPublicPdfUrlAttribute(): string
    {
        $this->ensurePdfToken();
        return url("/api/v1/toiture/devis/public/{$this->pdf_token}/pdf");
    }
}