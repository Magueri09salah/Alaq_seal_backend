<?php

namespace App\Services;

class ToitureCalculatorService
{
    /**
     * Calculate toiture devis based on specifications
     * 
     * Formulas from SIMULATEUR_TOITURE_DOCUMENT_COMPLET_DEFINITIF
     */
    public function calculate(array $data): array
    {
        $type = $data['type']; // toiture, mur, salle_bain
        
        if ($type === 'toiture') {
            return $this->calculateToiture($data);
        } elseif ($type === 'mur') {
            return $this->calculateMur($data);
        } elseif ($type === 'salle_bain') {
            return $this->calculateSalleBain($data);
        }
        
        throw new \Exception('Type non reconnu');
    }
    
    /**
     * Calculate toiture (full logic from document)
     */
    private function calculateToiture(array $data): array
    {
        $longueur = (float) $data['longueur'];
        $largeur = (float) $data['largeur'];
        $perimetre = (float) $data['perimetre'];
        $hauteur_acrotere = (float) ($data['hauteur_acrotere'] ?? 0);
        $nombre_evacuations = (int) ($data['nombre_evacuations'] ?? 1);
        $chape_existante = $data['chape_existante'] ?? true;
        
        $toiture_type = $data['toiture_type']; // accessible / non_accessible
        $isolation = $data['isolation']; // true / false
        $finition = $data['finition'] ?? 'autoprotegee'; // autoprotegee / lestage (for non_accessible)
        
        // ── FORMULES GÉNÉRALES ──────────────────────────────────────────
        $surface_brute = $longueur * $largeur;
        $surface_releves = $perimetre * $hauteur_acrotere;
        $surface_technique = ($surface_brute * 1.10) + $surface_releves;
        
        // ── BUILD MATERIALS LIST ────────────────────────────────────────
        $materials = [];
        $order = 1;
        
        // 0. Chape de pente (si inexistante)
        if (!$chape_existante) {
            $materials[] = [
                'order' => $order++,
                'name' => 'Chape de pente béton',
                'quantity' => round($surface_brute * 0.03, 2),
                'unit' => 'm³',
            ];
        }
        
        // 1. Primaire bitumineux
        $materials[] = [
            'order' => $order++,
            'name' => 'Primaire bitumineux d\'accrochage',
            'quantity' => round($surface_brute * 0.3, 2),
            'unit' => 'L',
        ];
        
        // 2. Pare-vapeur (si isolation)
        if ($isolation) {
            $materials[] = [
                'order' => $order++,
                'name' => 'Pare-vapeur bitumineux soudable aluminium',
                'quantity' => round($surface_technique / 8, 2),
                'unit' => 'rouleaux',
            ];
        }
        
        // 3. Isolation thermique (si isolation)
        if ($isolation) {
            $materials[] = [
                'order' => $order++,
                'name' => 'Isolation thermique XPS ou Polyuréthane',
                'quantity' => round($surface_brute, 2),
                'unit' => 'm²',
            ];
        }
        
        // 4. Membrane SBS sous-couche 4mm
        $materials[] = [
            'order' => $order++,
            'name' => 'Membrane bitumineuse SBS sous-couche 4mm armée polyester',
            'quantity' => round($surface_technique / 8, 2),
            'unit' => 'rouleaux',
        ];
        
        // 5. Finition selon type
        if ($toiture_type === 'accessible') {
            // Toiture accessible → Membrane finition + Chape
            $materials[] = [
                'order' => $order++,
                'name' => 'Membrane bitumineuse finition pour chape',
                'quantity' => round($surface_technique / 8, 2),
                'unit' => 'rouleaux',
            ];
            $materials[] = [
                'order' => $order++,
                'name' => 'Chape béton 3cm (carrelage non fourni)',
                'quantity' => round($surface_brute * 0.03, 2),
                'unit' => 'm³',
            ];
        } else {
            // Toiture non accessible → Finition selon choix
            if ($finition === 'autoprotegee') {
                $materials[] = [
                    'order' => $order++,
                    'name' => 'Membrane autoprotégée ardoisée',
                    'quantity' => round($surface_technique / 8, 2),
                    'unit' => 'rouleaux',
                ];
            } else {
                // Finition lisse + Géotextile + Lestage
                $materials[] = [
                    'order' => $order++,
                    'name' => 'Membrane finition lisse',
                    'quantity' => round($surface_technique / 8, 2),
                    'unit' => 'rouleaux',
                ];
                $materials[] = [
                    'order' => $order++,
                    'name' => 'Géotextile anti-poinçonnement',
                    'quantity' => round($surface_brute, 2),
                    'unit' => 'm²',
                ];
                $materials[] = [
                    'order' => $order++,
                    'name' => 'Gravier lestage 5cm (40/60)',
                    'quantity' => round($surface_brute * 0.05, 2),
                    'unit' => 'm³',
                ];
            }
        }
        
        // 6. Relevés
        $materials[] = [
            'order' => $order++,
            'name' => 'Relevés d\'étanchéité',
            'quantity' => round($perimetre, 2),
            'unit' => 'ml',
        ];
        
        // 7. Naissances
        $materials[] = [
            'order' => $order++,
            'name' => 'Naissances EP (évacuations)',
            'quantity' => $nombre_evacuations,
            'unit' => 'unités',
        ];
        
        // ── PRICING (SIMPLIFIED - Adjust based on your pricing) ──────────
        $total_ht = $surface_brute * 60; // Example: 60 MAD/m²
        
        return [
            'type' => 'toiture',
            'toiture_type' => $toiture_type,
            'isolation' => $isolation,
            'finition' => $finition,
            'surface_brute' => round($surface_brute, 2),
            'surface_technique' => round($surface_technique, 2),
            'surface_releves' => round($surface_releves, 2),
            'materials' => $materials,
            'total_ht' => round($total_ht, 2),
        ];
    }
    
    /**
     * Calculate mur enterré
     */
    private function calculateMur(array $data): array
    {
        $longueur = (float) $data['longueur'];
        $largeur = (float) $data['largeur'];
        $hauteur = (float) ($data['hauteur'] ?? 3);
        $nombre_murs = (int) ($data['nombre_murs'] ?? 4);
        
        $surface_brute = $longueur * $hauteur * $nombre_murs;
        
        $materials = [];
        $materials[] = [
            'order' => 1,
            'name' => 'Barbotine d\'accrochage',
            'quantity' => round($surface_brute * 0.5, 2),
            'unit' => 'kg',
        ];
        $materials[] = [
            'order' => 2,
            'name' => 'Mortier hydrofuge couche 1',
            'quantity' => round($surface_brute * 2.5, 2),
            'unit' => 'kg',
        ];
        $materials[] = [
            'order' => 3,
            'name' => 'Bande d\'angle armée',
            'quantity' => round($longueur * $nombre_murs * 0.2, 2),
            'unit' => 'ml',
        ];
        $materials[] = [
            'order' => 4,
            'name' => 'Mortier hydrofuge couche 2',
            'quantity' => round($surface_brute * 2.5, 2),
            'unit' => 'kg',
        ];
        $materials[] = [
            'order' => 5,
            'name' => 'Mortier hydrofuge couche 3',
            'quantity' => round($surface_brute * 1.5, 2),
            'unit' => 'kg',
        ];
        
        $total_ht = $surface_brute * 45; // Example: 45 MAD/m²
        
        return [
            'type' => 'mur',
            'surface_brute' => round($surface_brute, 2),
            'materials' => $materials,
            'total_ht' => round($total_ht, 2),
        ];
    }
    
    /**
     * Calculate salle de bain (sous carrelage)
     */
    private function calculateSalleBain(array $data): array
    {
        $longueur = (float) $data['longueur'];
        $largeur = (float) $data['largeur'];
        
        $surface_brute = $longueur * $largeur;
        
        $materials = [];
        $materials[] = [
            'order' => 1,
            'name' => 'Primaire d\'accrochage SEL',
            'quantity' => round($surface_brute * 0.2, 2),
            'unit' => 'kg',
        ];
        $materials[] = [
            'order' => 2,
            'name' => 'Résine d\'étanchéité SEL couche 1',
            'quantity' => round($surface_brute * 1.2, 2),
            'unit' => 'kg',
        ];
        $materials[] = [
            'order' => 3,
            'name' => 'Bande d\'étanchéité périphérique',
            'quantity' => round(($longueur + $largeur) * 2, 2),
            'unit' => 'ml',
        ];
        $materials[] = [
            'order' => 4,
            'name' => 'Résine d\'étanchéité SEL couche 2',
            'quantity' => round($surface_brute * 1.2, 2),
            'unit' => 'kg',
        ];
        
        $total_ht = $surface_brute * 35; // Example: 35 MAD/m²
        
        return [
            'type' => 'salle_bain',
            'surface_brute' => round($surface_brute, 2),
            'materials' => $materials,
            'total_ht' => round($total_ht, 2),
        ];
    }
}