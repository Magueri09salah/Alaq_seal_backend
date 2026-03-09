<?php

namespace App\Services;

class ToitureCalculatorService
{
    /**
     * Calculate toiture devis based on specifications
     * 
     * Formulas from SIMULATEUR_TOITURE_DOCUMENT_COMPLET_DEFINITIF
     */
    public function calculate(array $data)
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
        // $perimetre = (float) $data['perimetre'];
        $hauteur_acrotere = (float) ($data['hauteur_acrotere'] ?? 0);
        $nombre_evacuations = (int) ($data['nombre_evacuations'] ?? 1);
        $chape_existante = $data['chape_existante'] ?? true;
        
        $toiture_type = $data['toiture_type']; // accessible / non_accessible
        $isolation = $data['isolation']; // true / false
        $finition = $data['finition'] ?? 'autoprotegee'; // autoprotegee / lestage (for non_accessible)
        
        // ── FORMULES GÉNÉRALES ──────────────────────────────────────────
        $surface_brute = $longueur * $largeur;
        $surface_releves =($longueur + $largeur) * 2 * $hauteur_acrotere;
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
            'name' => 'Membrane bitumineuse SBS sous-couche 2.5mm armée polyester',
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
                    'quantity' => round($surface_brute + ($surface_brute * 0.1) , 2),
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
        // $materials[] = [
        //     'order' => $order++,
        //     'name' => 'Relevés d\'étanchéité',
        //     'quantity' => round($perimetre, 2),
        //     'unit' => 'ml',
        // ];
        
        // 6. Naissances
        $materials[] = [
            'order' => $order++,
            'name' => 'Kit siphon',
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
    private function calculateMur(array $data)
    {
        // Input data
        $longueur = (float) $data['longueur'];
        $hauteur = (float) ($data['hauteur'] ?? 3);
        $water_level = $data['water_level'] ?? 'humidite'; // humidite, infiltration, nappe
        $drain = (bool) ($data['drain'] ?? false);
        
        // ── FORMULES GÉNÉRALES ──────────────────────────────────────────
        $hauteur_technique = $hauteur + 0.15; // Relevé d'étanchéité
        $surface_mur = $longueur * $hauteur_technique;
        $surface_technique_membr = $surface_mur * 1.05; // Recouvrements
        
        // ── BUILD MATERIALS LIST ────────────────────────────────────────
        $materials = [];
        $order = 1;
        // Different products based on water level
        if ($water_level === 'humidite') {

            // CAS 1: HUMIDITÉ DU SOL
            // Primaire + Enduit bitumineux (2 couches) + Nappe Delta MS
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Primaire bitumineux d\'accrochage',
                'quantity' => round($surface_mur * 0.3, 2),
                'unit' => 'L',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Enduit bitumineux d\'étanchéité (2 couches)',
                'quantity' => round($surface_mur * 2, 2),
                'unit' => 'kg',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Nappe à excroissances Delta MS',
                'quantity' => round($surface_mur, 2),
                'unit' => 'm²',
            ];

            
        } elseif ($water_level === 'infiltration') {

            // CAS 2: INFILTRATION D'EAU OCCASIONNELLE
            // Primaire + Membrane SBS monocouche + Nappe drainante

            $materials[] = [
                'order' => $order++,
                'name' => 'Primaire bitumineux',
                'quantity' => round($surface_mur * 0.3, 2),
                'unit' => 'L',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Membrane bitumineuse SBS monocouche',
                'quantity' => round($surface_technique_membr / 8, 2),
                'unit' => 'rouleaux',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Nappe drainante',
                'quantity' => round($surface_mur, 2),
                'unit' => 'm²',
            ];
            
        } else { // nappe
            // CAS 3: NAPPE PHRÉATIQUE (Eau permanente)
            // Primaire + Membrane SBS sous-couche + Membrane SBS finition + Protection + Drainage OBLIGATOIRE

            $materials[] = [
                'order' => $order++,
                'name' => 'Primaire bitumineux',
                'quantity' => round($surface_mur * 0.3, 2),
                'unit' => 'L',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Membrane SBS sous-couche',
                'quantity' => round($surface_technique_membr / 8, 2),
                'unit' => 'rouleaux',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Membrane SBS finition',
                'quantity' => round($surface_technique_membr / 8, 2),
                'unit' => 'rouleaux',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Nappe drainante ou panneaux XPS',
                'quantity' => round($surface_mur, 2),
                'unit' => 'm²',
            ];
            
            // For nappe phréatique, drainage is MANDATORY
            $drain = true;
        }
        // return $drain;
        // ── DRAINAGE (if selected or mandatory) ─────────────────────────
        if ($drain) {
            $materials[] = [
                'order' => $order++,
                'name' => 'Géotextile',
                'quantity' => round($surface_mur, 2),
                'unit' => 'm²',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Drain perforé',
                'quantity' => round($longueur, 2),
                'unit' => 'ml',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Gravier drainant',
                'quantity' => round($longueur * 0.2, 2),
                'unit' => 'm³',
            ];
        }
        
        // ── PRICING ──────────────────────────────────────────────────────
        // Base price per m² varies by water level
        $prix_m2 = [
            'humidite' => 45,      // MAD/m²
            'infiltration' => 65,  // MAD/m²
            'nappe' => 95,         // MAD/m²
            ];
            
            $total_ht = $surface_mur * $prix_m2[$water_level];
            
            // Add drainage cost if applicable
            if ($drain) {
                $total_ht += $longueur * 30; // 30 MAD/ml for drainage
            }
        
        return [
            'type' => 'mur',
            'water_level' => $water_level,
            'drain' => $drain,
            'longueur' => round($longueur, 2),
            'hauteur' => round($hauteur, 2),
            'hauteur_technique' => round($hauteur_technique, 2),
            'surface_brute' => round($surface_mur, 2),
            'surface_technique' => round($surface_technique_membr, 2),
            'materials' => $materials,
            'total_ht' => round($total_ht, 2),
        ];
    }
    
    /**
     * Calculate salle de bain (sous carrelage)
     */
    private function calculateSalleBain(array $data): array
    {
        // Input data
        $longueur = (float) $data['longueur'];
        $largeur = (float) $data['largeur'];
        $sdb_type = $data['sdb_type'] ?? 'avec_bac'; // avec_bac, italienne
        
        // ── FORMULES ────────────────────────────────────────────────────
        $surface_brute = $longueur * $largeur;
        $perimetre = ($longueur + $largeur) * 2;
        
        // ── BUILD MATERIALS LIST ────────────────────────────────────────
        $materials = [];
        $order = 1;
        
        if ($sdb_type === 'avec_bac') {
            // DOUCHE AVEC BAC (Standard SEL)
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Primaire d\'accrochage SEL',
                'quantity' => round($surface_brute * 0.2, 2),
                'unit' => 'kg',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Bande d\'étanchéité angles sol/mur',
                'quantity' => round($perimetre, 2),
                'unit' => 'ml',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Bande d\'étanchéité angles verticaux',
                'quantity' => round($perimetre * 0.3, 2), // Estimated vertical corners
                'unit' => 'ml',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Résine d\'étanchéité SEL liquide flexible (2 couches)',
                'quantity' => round($surface_brute * 1.5, 2),
                'unit' => 'kg',
            ];
            
        } else { // italienne
            // DOUCHE ITALIENNE (SEL renforcé)
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Primaire d\'accrochage SEL',
                'quantity' => round($surface_brute * 0.2, 2),
                'unit' => 'kg',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Bande d\'étanchéité angles sol/mur',
                'quantity' => round($perimetre, 2),
                'unit' => 'ml',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Bande d\'étanchéité angles verticaux',
                'quantity' => round($perimetre * 0.3, 2),
                'unit' => 'ml',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Manchette d\'étanchéité siphon',
                'quantity' => 1,
                'unit' => 'unité',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Résine d\'étanchéité SEL liquide renforcé zone douche (2 couches)',
                'quantity' => round($surface_brute * 1.8, 2),
                'unit' => 'kg',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Kit siphon central',
                'quantity' => 1,
                'unit' => 'unité',
            ];
        }
        
        // ── PRICING ──────────────────────────────────────────────────────
        $prix_m2 = [
            'avec_bac' => 35,    // MAD/m²
            'italienne' => 50,   // MAD/m² (more expensive - reinforced system)
        ];
        
        $total_ht = $surface_brute * $prix_m2[$sdb_type];
        
        return [
            'type' => 'salle_bain',
            'sdb_type' => $sdb_type,
            'longueur' => round($longueur, 2),
            'largeur' => round($largeur, 2),
            'surface_brute' => round($surface_brute, 2),
            'perimetre' => round($perimetre, 2),
            'materials' => $materials,
            'total_ht' => round($total_ht, 2),
        ];
    }
}