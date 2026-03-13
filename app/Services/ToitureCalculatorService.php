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
                'quantity' => ceil($surface_technique / 8),
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
            'quantity' => ceil($surface_technique / 10),
            'unit' => 'rouleaux',
        ];
        
        // 5. Finition selon type
        if ($toiture_type === 'accessible') {
            // Toiture accessible → Membrane finition + Chape
            $materials[] = [
                'order' => $order++,
                'name' => 'Membrane bitumineuse finition pour chape',
                'quantity' => ceil($surface_technique / 10),
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
                    'quantity' => ceil($surface_technique / 10),
                    'unit' => 'rouleaux',
                ];
            } else {
                // Finition lisse + Géotextile + Lestage
                $materials[] = [
                    'order' => $order++,
                    'name' => 'Membrane finition lisse',
                    'quantity' => ceil($surface_technique / 10),
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
                'quantity' => ceil($surface_technique_membr / 10),
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
                'quantity' => ceil($surface_technique_membr / 10),
                'unit' => 'rouleaux',
            ];
            
            $materials[] = [
                'order' => $order++,
                'name' => 'Membrane SBS finition',
                'quantity' => ceil($surface_technique_membr / 10),
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
        $type = $data['sdb_type'];
        $support = $data['support'];

        $surface_sol_totale = (float) $data['surface_sol_totale'];
        $surface_etancheifiee = $surface_sol_totale; // will adjust for avec_bac
        $surface_murs = 0;
        $perimetre_sol = 0;
        $bandes_verticales = 0;
        $bandes = 0;

        if($type === 'avec_bac'){
            $surface_bac = (float) ($data['surface_bac'] ?? 0);
            $longueur = (float) $data['longueur_murs'];
            $largeur = (float) $data['largeur_murs'];
            $hauteur = (float) $data['hauteur_murs'];

            $surface_etancheifiee = $surface_sol_totale - $surface_bac;
            $surface_murs = ($longueur + $largeur) * 2 * $hauteur;
            $perimetre_sol = ($longueur + $largeur) * 2;
            $angles_verticales = 4 * $hauteur; // 4 corners
            $bandes = $perimetre_sol + $angles_verticales;
        }else{ // italienne
            $surface_zone_douche = (float) $data['surface_zone_douche'];
            $l_douche = (float) $data['longueur_murs_douche'];
            $l_douche_larg = (float) $data['largeur_murs_douche'];
            $h_douche = (float) $data['hauteur_murs_douche'];
            $l_piece = (float) $data['longueur_murs_piece'];
            $l_piece_larg = (float) $data['largeur_murs_piece'];
            $h_piece = (float) $data['hauteur_murs_piece'];

            $surface_murs_douche = ($l_douche + $l_douche_larg) * 2 * $h_douche;
            $surface_murs_piece = ($l_piece + $l_piece_larg) * 2 * $h_piece;
            $surface_murs = $surface_murs_douche + $surface_murs_piece;
            // For total area, we use the full floor (surface_sol_totale) – the zone douche is part of it
            $perimetre_sol = ($l_piece + $l_piece_larg) * 2;
            $angles_verticales = 4 * $h_piece; // room corners
            $angles_douche = 4 * $h_douche; // shower corners (if separate)
            $bandes = $perimetre_sol + $angles_verticales + $angles_douche;
        }

        $surface_totale = $surface_etancheifiee + $surface_murs;
          // Primer selection
        $primer_name = match ($support) {
            'ciment' => 'Primaire acrylique support absorbant',
            'carrelage' => $type === 'avec_bac'
                ? "Primaire d'adhérence support lisse"
                : "Primaire époxy d'accrochage",
        };

         // SEL product name
        $sel_name = $type === 'avec_bac'
            ? 'SEL (Système d’Étanchéité Liquide) liquide flexible (2 couches)'
            : 'SEL (Système d’Étanchéité Liquide) liquide renforcé zone douche (2 couches)';

        // Build materials list
        $materials = [];
        $order = 1;

        $materials[] = [
            'order' => $order++,
            'name' => $primer_name,
            'quantity' => round($surface_totale * 0.2, 2),
            'unit' => 'L',
        ];

        $materials[] = [
            'order' => $order++,
            'name' => 'Bandes',
            'quantity' => round($bandes, 2),
            'unit' => 'ml',
        ];

        // $materials[] = [
        //     'order' => $order++,
        //     'name' => 'Bandes angles verticaux',
        //     'quantity' => round($bandes_verticales, 2),
        //     'unit' => 'ml',
        // ];

        if ($type === 'italienne') {
            // if ($angles_douche > 0) {
            //     $materials[] = [
            //         'order' => $order++,
            //         'name' => 'Bandes',
            //         'quantity' => round($bandes, 2),
            //         'unit' => 'ml',
            //     ];
            // }

            $materials[] = [
                'order' => $order++,
                'name' => "Manchette d'étanchéité siphon",
                'quantity' => 1,
                'unit' => 'unité',
            ];

            $materials[] = [
                'order' => $order++,
                'name' => $sel_name,
                'quantity' => round($surface_totale * 2, 2),
                'unit' => 'kg',
            ];

            $materials[] = [
                'order' => $order++,
                'name' => 'Kit siphon',
                'quantity' => 1,
                'unit' => 'unité',
            ];
        } else {
            $materials[] = [
                'order' => $order++,
                'name' => $sel_name,
                'quantity' => round($surface_totale * 2, 2),
                'unit' => 'kg',
            ];
        }

        // Pricing (example – adjust as needed)
        $prix_m2 = $type === 'avec_bac' ? 35 : 50;
        $total_ht = $surface_totale * $prix_m2;

        return [
            'type' => 'salle_bain',
            'sdb_type' => $type,
            'support' => $support,
            'surface_brute' => round($surface_totale, 2),
            'surface_technique' => round($surface_totale, 2), // same for salle_bain
            'materials' => $materials,
            'total_ht' => round($total_ht, 2),
        ];
    }
}