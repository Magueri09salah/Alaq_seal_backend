<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Service, Product, ProductCase, CaseMaterial};

class EtancheiteProductsSeeder extends Seeder
{
    public function run(): void
    {
        $service = Service::where('code', 'etancheite')->firstOrFail();

        // ── 1. CUVELAGE INTÉRIEUR (CSTB) ─────────────────────────
        $cuvelage = Product::updateOrCreate(['code' => 'cuvelage_interieur'], [
            'service_id'    => $service->id,
            'name'          => 'Cuvelage Intérieur',
            'subcategory'   => 'mur',
            'category'      => 'standard',
            'price_min'     => 150, 'price_max' => 220,
            'price_unit'    => 'm2', 'warranty_years' => 10,
            'norme'         => 'CSTB',
            'description'   => 'Cuvelage intérieur en pression négative — conforme CSTB',
            'devis_text'    => "Réalisation d'un cuvelage intérieur par application d'une barbotine d'accrochage suivie de trois couches de mortier hydrofuge avec traitement des angles, conforme aux prescriptions CSTB.",
            'is_active'     => true, 'display_order' => 1,
        ]);

        // Case 1: Support sain
        $cuvelageSain = ProductCase::updateOrCreate(
            ['product_id' => $cuvelage->id, 'code' => 'sain'],
            ['name' => 'Support sain', 'description' => 'Mur en bon état, sans fissures ni décollements. Application directe après nettoyage.', 'icon_type' => 'check', 'display_order' => 1]
        );
        $this->seedMaterials($cuvelageSain->id, [
            [1, "Barbotine d'accrochage", 'barbotine', 'surface',   2,   'kg'],
            [2, 'Mortier hydrofuge couche 1', 'mortier', 'surface', 4,   'kg'],
            [3, "Bande d'angle",          'bande',     'perimetre', 1,   'ml'],
            [4, 'Mortier hydrofuge couche 2', 'mortier', 'surface', 4,   'kg'],
            [5, 'Mortier hydrofuge couche 3', 'mortier', 'surface', 3,   'kg'],
        ]);

        // Case 2: Support fissuré
        $cuvelageFissure = ProductCase::updateOrCreate(
            ['product_id' => $cuvelage->id, 'code' => 'fissure'],
            ['name' => 'Support fissuré', 'description' => 'Mur présentant des fissures. Nécessite une réparation préalable avant cuvelage.', 'icon_type' => 'warning', 'display_order' => 2]
        );
        $this->seedMaterials($cuvelageFissure->id, [
            [1, 'Mortier de réparation',  'mortier',   'surface',   2,   'kg'],
            [2, "Barbotine d'accrochage", 'barbotine', 'surface',   2,   'kg'],
            [3, 'Mortier hydrofuge couche 1', 'mortier', 'surface', 4,   'kg'],
            [4, "Bande d'angle",          'bande',     'perimetre', 1,   'ml'],
            [5, 'Mortier hydrofuge couche 2', 'mortier', 'surface', 4,   'kg'],
            [6, 'Mortier hydrofuge couche 3', 'mortier', 'surface', 3,   'kg'],
        ]);

        // ── 2. SEL MUR SALLE D'EAU (DTU 52.2) ────────────────────
        $selMur = Product::updateOrCreate(['code' => 'sel_mur_salle_eau'], [
            'service_id'    => $service->id,
            'name'          => "SEL Mur Salle d'Eau",
            'subcategory'   => 'mur',
            'category'      => 'premium',
            'price_min'     => 180, 'price_max' => 280,
            'price_unit'    => 'm2', 'warranty_years' => 15,
            'norme'         => 'DTU 52.2',
            'description'   => "Étanchéité murs salle d'eau par résine SEL — DTU 52.2 + CSTB",
            'devis_text'    => "Étanchéité des murs de salle d'eau par primaire et deux couches de résine SEL avec traitement des angles, conforme au DTU 52.2.",
            'is_active'     => true, 'display_order' => 2,
        ]);

        // Case 1: Mur standard
        $selMurStd = ProductCase::updateOrCreate(
            ['product_id' => $selMur->id, 'code' => 'standard'],
            ['name' => 'Mur standard', 'description' => 'Mur de salle d\'eau sans obstacles. Hauteur minimale 2m. Relevé sol/mur ≥ 10 cm.', 'icon_type' => 'check', 'display_order' => 1]
        );
        $this->seedMaterials($selMurStd->id, [
            [1, 'Primaire',              'primaire', 'surface',   0.3, 'kg'],
            [2, 'Résine SEL couche 1',   'resine',   'surface',   1,   'kg'],
            [3, "Bande d'angle",         'bande',    'perimetre', 1,   'ml'],
            [4, 'Résine SEL couche 2',   'resine',   'surface',   1,   'kg'],
        ]);

        // Case 2: Mur avec tuyaux
        $selMurTuyaux = ProductCase::updateOrCreate(
            ['product_id' => $selMur->id, 'code' => 'tuyaux'],
            ['name' => 'Mur avec tuyaux', 'description' => 'Mur avec traversées de tuyauteries. Nécessite des colliers étanches à chaque traversée.', 'icon_type' => 'water', 'display_order' => 2]
        );
        $this->seedMaterials($selMurTuyaux->id, [
            [1, 'Primaire',              'primaire', 'surface',   0.3, 'kg'],
            [2, 'Résine SEL couche 1',   'resine',   'surface',   1,   'kg'],
            [3, "Bande d'angle",         'bande',    'perimetre', 1,   'ml'],
            [4, 'Résine SEL couche 2',   'resine',   'surface',   1,   'kg'],
            [5, 'Collier étanche',       'collier',  'unite',     1,   'unité', true], // optional — qty varies
        ]);

        // ── 3. MUR ENTERRÉ BITUMINEUX (DTU 20.1) ─────────────────
        $murEnterre = Product::updateOrCreate(['code' => 'mur_enterre_bitumineux'], [
            'service_id'    => $service->id,
            'name'          => 'Mur Enterré Bitumineux',
            'subcategory'   => 'mur',
            'category'      => 'standard',
            'price_min'     => 120, 'price_max' => 200,
            'price_unit'    => 'm2', 'warranty_years' => 10,
            'norme'         => 'DTU 20.1',
            'description'   => 'Étanchéité murs enterrés par enduit bitumineux — DTU 20.1',
            'devis_text'    => 'Étanchéité des murs enterrés par primaire bitumineux, enduit bitumineux et nappe de protection, conforme au DTU 20.1.',
            'is_active'     => true, 'display_order' => 3,
        ]);

        // Case 1: Sans drainage
        $murSansDrain = ProductCase::updateOrCreate(
            ['product_id' => $murEnterre->id, 'code' => 'sans_drainage'],
            ['name' => 'Sans drainage', 'description' => 'Sol avec bonne perméabilité naturelle. Pas de nappe phréatique à proximité.', 'icon_type' => 'check', 'display_order' => 1]
        );
        $this->seedMaterials($murSansDrain->id, [
            [1, 'Primaire bitumineux',  'primaire', 'surface',  0.3, 'L'],
            [2, 'Enduit bitumineux',    'enduit',   'surface',  3,   'kg'],
            [3, 'Nappe de protection',  'nappe',    'surface',  1,   'm²'],
        ]);

        // Case 2: Avec drainage
        $murAvecDrain = ProductCase::updateOrCreate(
            ['product_id' => $murEnterre->id, 'code' => 'avec_drainage'],
            ['name' => 'Avec drainage', 'description' => 'Terrain argileux ou zone humide. Nécessite drain + géotextile avant remblai.', 'icon_type' => 'water', 'display_order' => 2]
        );
        $this->seedMaterials($murAvecDrain->id, [
            [1, 'Primaire bitumineux',  'primaire',    'surface',  0.3, 'L'],
            [2, 'Enduit bitumineux',    'enduit',      'surface',  3,   'kg'],
            [3, 'Nappe de protection',  'nappe',       'surface',  1,   'm²'],
            [4, 'Drain périphérique',   'drain',       'longueur', 1,   'ml'],
            [5, 'Géotextile',           'geotextile',  'longueur', 1,   'ml'],
        ]);

        // ── 4. SEL SOL SALLE D'EAU (DTU 52.2) ───────────────────
        $selSol = Product::updateOrCreate(['code' => 'sel_sol_salle_eau'], [
            'service_id'    => $service->id,
            'name'          => "SEL Sol Salle d'Eau",
            'subcategory'   => 'sol',
            'category'      => 'premium',
            'price_min'     => 160, 'price_max' => 260,
            'price_unit'    => 'm2', 'warranty_years' => 15,
            'norme'         => 'DTU 52.2',
            'description'   => 'Étanchéité sol sous carrelage par résine SEL — DTU 52.2 + CSTB',
            'devis_text'    => "Réalisation d'un SEL sous carrelage avec primaire, deux couches de résine et traitement des angles, conforme au DTU 52.2.",
            'is_active'     => true, 'display_order' => 4,
        ]);

        // Case 1: Sol standard
        $selSolStd = ProductCase::updateOrCreate(
            ['product_id' => $selSol->id, 'code' => 'standard'],
            ['name' => 'Sol standard', 'description' => 'Sol de douche ou salle de bain sans siphon encastré. Relevé périphérique ≥ 10 cm.', 'icon_type' => 'check', 'display_order' => 1]
        );
        $this->seedMaterials($selSolStd->id, [
            [1, 'Primaire',              'primaire', 'surface',   0.3, 'kg'],
            [2, 'Résine SEL couche 1',   'resine',   'surface',   1.2, 'kg'],
            [3, 'Bande périphérique',    'bande',    'perimetre', 1,   'ml'],
            [4, 'Résine SEL couche 2',   'resine',   'surface',   1.3, 'kg'],
        ]);

        // Case 2: Sol avec siphon
        $selSolSiphon = ProductCase::updateOrCreate(
            ['product_id' => $selSol->id, 'code' => 'siphon'],
            ['name' => 'Sol avec siphon', 'description' => 'Sol avec siphon de douche encastré. Nécessite une bande de siphon supplémentaire.', 'icon_type' => 'water', 'display_order' => 2]
        );
        $this->seedMaterials($selSolSiphon->id, [
            [1, 'Primaire',              'primaire', 'surface',   0.3, 'kg'],
            [2, 'Résine SEL couche 1',   'resine',   'surface',   1.2, 'kg'],
            [3, 'Bande périphérique',    'bande',    'perimetre', 1,   'ml'],
            [4, 'Résine SEL couche 2',   'resine',   'surface',   1.3, 'kg'],
            [5, 'Bande siphon',          'bande',    'unite',     1,   'unité'],
        ]);

        $this->command->info('✅ 4 Étanchéité products seeded (3 Mur, 1 Sol) with DTU cases & materials');
    }

    private function seedMaterials(int $caseId, array $materials): void
    {
        foreach ($materials as $mat) {
            CaseMaterial::updateOrCreate(
                ['product_case_id' => $caseId, 'step_order' => $mat[0]],
                [
                    'name'           => $mat[1],
                    'type'           => $mat[2],
                    'formula_type'   => $mat[3],
                    'formula_factor' => $mat[4],
                    'unit'           => $mat[5],
                    'is_optional'    => $mat[6] ?? false,
                ]
            );
        }
    }
}