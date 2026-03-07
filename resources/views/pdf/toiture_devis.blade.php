<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Devis {{ $devis->devis_number }}</title>
    <style>
        @page { 
            margin: 16mm 12mm 20mm 12mm; 
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0f172a; 
            font-size: 11px;
            line-height: 1.4;
            padding: 50px;
        }
        
        /* Logo */
        .logo {
            max-width: 150px;
            max-height: 60px;
            margin-bottom: 10px;
            display: block;
        }
        
        /* Colors */
        .accent { color: #3b82f6; }
        .muted { color: #64748b; }
        .bg-accent { background-color: #3b82f6; color: white; }
        
        /* Layout */
        .row { 
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        .col { 
            display: table-cell;
            vertical-align: top;
            padding-right: 12px;
        }
        .col:last-child { padding-right: 0; }
        .col-half { width: 50%; }
        
        /* Header */
        .header {
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: 900;
            color: #3b82f6;
            margin-bottom: 8px;
        }
        .company-info {
            font-size: 10px;
            color: #64748b;
            line-height: 1.5;
        }
        
        /* Quote badge */
        .quote-badge {
            text-align: right;
        }
        .quote-title {
            font-size: 28px;
            font-weight: 900;
            color: #3b82f6;
            letter-spacing: 1px;
        }
        .quote-meta {
            margin-top: 8px;
        }
        .pill { 
            display: inline-block; 
            border: 1px solid #e2e8f0; 
            border-radius: 999px; 
            padding: 4px 12px; 
            margin: 2px;
            font-size: 10px;
            background: #f8fafc;
        }
        
        /* Cards */
        .card { 
            border: 1px solid #e2e8f0; 
            border-radius: 10px;
            padding: 12px;
            background: #ffffff;
            margin-bottom: 12px;
        }
        .card-title { 
            font-size: 11px; 
            font-weight: 800; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #3b82f6;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        /* Tables */
        table { 
            width: 100%; 
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10px;
        }
        thead th {
            background: #3b82f6;
            color: white;
            padding: 10px 8px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
        }
        thead th:first-child { 
            border-top-left-radius: 8px;
        }
        thead th:last-child { 
            border-top-right-radius: 8px;
        }
        tbody td { 
            border-bottom: 1px solid #e2e8f0;
            padding: 8px;
            vertical-align: top;
        }
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        
        /* Totals */
        .totals-wrapper {
            margin-top: 20px;
            margin-left: auto;
            width: 280px;
        }
        .totals { 
            border: 2px solid #3b82f6;
            border-radius: 10px;
            overflow: hidden;
        }
        .total-line { 
            display: table;
            width: 100%;
            padding: 10px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .total-line:last-child {
            border-bottom: none;
        }
        .total-label {
            display: table-cell;
            color: #64748b;
        }
        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: 600;
        }
        .total-grand { 
            background: #3b82f6;
            color: white;
        }
        .total-grand .total-label {
            color: white;
            font-weight: 700;
            font-size: 12px;
        }
        .total-grand .total-value {
            font-size: 16px;
            font-weight: 900;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 5mm;
            left: 12mm;
            right: 12mm;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            text-align: center;
        }
        
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>

@php
    $fmtMoney = fn($n) => number_format((float)$n, 2, ',', ' ') . ' MAD';
    $fmtQty = fn($n) => rtrim(rtrim(number_format((float)$n, 2, ',', ' '), '0'), ',');
    
    $companyName = "ALAQ SEAL VISION";
    $companyAddress = "25, zone industrielle Sidi Ghanem -3 40010 , Marrakech - Maroc";
    $companyCity = "Marrakech, Maroc";
    $companyPhone = "+212 7 67 91 54 25";
    $companyEmail = "contact@alaqsealvision.com";
    $companyIce = "003890458000001";

    // Logo handling - FIXED: Use correct MIME type for PNG
    $logoBase64 = null;
    $logoPath = public_path('images/alaq_seal_logo.png');
    
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        // CRITICAL FIX: seal.png is a PNG file, NOT SVG!
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    } else {
        // Try alternative paths
        $altPaths = [
            public_path('alaq_seal_logo.png'),
            public_path('alaq_seal_logo.png'),
            base_path('public/images/alaq_seal_logo.png'),
        ];
        
        foreach ($altPaths as $path) {
            if (file_exists($path)) {
                $logoData = file_get_contents($path);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                break;
            }
        }
    }
@endphp

{{-- ==================== HEADER ==================== --}}
<div class="header">
    <div class="row">
        <div class="col col-half">
            @if($logoBase64)
                <img class="logo" src="{{ $logoBase64 }}" alt="Logo {{ $companyName }}">
            @else
                {{-- Fallback to company name if logo not found --}}
                <div class="company-name">{{ $companyName }}</div>
            @endif
            <div class="company-info">
                {{ $companyAddress }}<br>
                Tél: {{ $companyPhone }}<br>
                {{ $companyEmail }}<br>
                <strong>ICE:</strong> {{ $companyIce }}
            </div>
        </div>
        <div class="col col-half quote-badge">
            <div class="quote-title">DEVIS</div>
            <div class="quote-meta">
                <span class="pill"><strong>N°</strong> {{ $devis->devis_number }}</span>
                <span class="pill"><strong>Date</strong> {{ $devis->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ==================== CLIENT & PROJET ==================== --}}
<div class="row">
    <div class="col col-half">
        <div class="card">
            <div class="card-title">Client</div>
            <div style="font-weight: 700; font-size: 12px; margin-bottom: 5px;">
                {{ $devis->user->name }}
            </div>
            <div class="muted">Email: {{ $devis->user->email }}</div>
            @if($devis->user->phone)
                <div class="muted">Tél: {{ $devis->user->phone }}</div>
            @endif
        </div>
    </div>
    <div class="col col-half">
        <div class="card">
            <div class="card-title">Projet</div>
            <div style="font-weight: 700; font-size: 12px; margin-bottom: 5px;">
                {{ $devis->project_name ?: 'Projet sans titre' }}
            </div>
            @if($devis->project_location)
                <div class="muted">Localisation: {{ $devis->project_location }}</div>
            @endif
            <div style="margin-top: 5px;">
                <span class="muted">Type:</span> {{ $devis->type_label }}
            </div>
            @if($devis->type === 'toiture')
                <div>
                    <span class="muted">Toiture:</span> {{ $devis->toiture_type_label }}
                </div>
                <div>
                    <span class="muted">Isolation:</span> {{ $devis->isolation ? 'Oui (Toiture chaude)' : 'Non' }}
                </div>
                @if($devis->finition)
                    <div>
                        <span class="muted">Finition:</span> {{ $devis->finition_label }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- ==================== DIMENSIONS ==================== --}}
<!-- <div class="card">
    <div class="card-title">Dimensions</div>
    <div class="row">
        <div class="col" style="width: 20%;">
            <div class="muted" style="font-size: 9px;">Longueur</div>
            <div style="font-weight: 700;">{{ $fmtQty($devis->longueur) }} m</div>
        </div>
        <div class="col" style="width: 20%;">
            <div class="muted" style="font-size: 9px;">Largeur</div>
            <div style="font-weight: 700;">{{ $fmtQty($devis->largeur) }} m</div>
        </div>
        @if($devis->type === 'toiture')
            @if($devis->perimetre)
                <div class="col" style="width: 20%;">
                    <div class="muted" style="font-size: 9px;">Périmètre</div>
                    <div style="font-weight: 700;">{{ $fmtQty($devis->perimetre) }} ml</div>
                </div>
            @endif
            @if($devis->hauteur_acrotere)
                <div class="col" style="width: 20%;">
                    <div class="muted" style="font-size: 9px;">H. acrotère</div>
                    <div style="font-weight: 700;">{{ $fmtQty($devis->hauteur_acrotere) }} m</div>
                </div>
            @endif
        @endif
        @if($devis->hauteur)
            <div class="col" style="width: 20%;">
                <div class="muted" style="font-size: 9px;">Hauteur</div>
                <div style="font-weight: 700;">{{ $fmtQty($devis->hauteur) }} m</div>
            </div>
        @endif
        <div class="col" style="width: 20%;">
            <div class="muted" style="font-size: 9px;">Surface totale</div>
            <div style="font-weight: 700; color: #3b82f6;">{{ $fmtQty($devis->surface_brute) }} m²</div>
        </div>
    </div>
</div> -->

{{-- ==================== MATÉRIAUX ==================== --}}
@if($devis->materials && count($devis->materials) > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 55%;">Désignation</th>
                <th style="width: 15%;" class="center">Quantité</th>
                <th style="width: 20%;" class="center">Unité</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devis->materials as $mat)
                <tr>
                    <td>{{ $mat['name'] }}</td>
                    <td class="center" style="font-weight: 600;">
                        {{ $fmtQty($mat['quantity']) }}
                    </td>
                    <td class="center">{{ $mat['unit'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- ==================== TOTAUX ==================== --}}
<div class="totals-wrapper no-break">
    <div class="totals">
        <div class="total-line">
            <span class="total-label">Surface ({{ $fmtQty($devis->surface_brute) }} m²)</span>
            <span class="total-value">{{ $fmtMoney($devis->total_ht) }}</span>
        </div>
        <div class="total-line">
            <span class="total-label">Sous-total HT</span>
            <span class="total-value">{{ $fmtMoney($devis->total_ht) }}</span>
        </div>
        <div class="total-line">
            <span class="total-label">TVA ({{ $devis->tva_rate }}%)</span>
            <span class="total-value">{{ $fmtMoney($devis->tva_amount) }}</span>
        </div>
        <div class="total-line total-grand">
            <span class="total-label">TOTAL TTC</span>
            <span class="total-value">{{ $fmtMoney($devis->total_ttc) }}</span>
        </div>
    </div>
</div>

{{-- ==================== NOTES ==================== --}}
@if($devis->notes)
    <div class="card" style="margin-top: 20px;">
        <div class="card-title">Notes</div>
        <p style="line-height: 1.6;">{{ $devis->notes }}</p>
    </div>
@endif

<div style="margin-top: 30px; page-break-inside: avoid;">
    <p style="font-weight: 700; font-size: 12px; margin-bottom: 5px;">Note importante</p>
    <!-- <p style="line-height: 1.6;">La durée de validité de ce devis est de 30 jours.</p> -->
    @if($devis->type === 'toiture')
        <p style="line-height: 1.6;">Les calculs et quantités sont conformes aux normes DTU 43.1.</p>
    @endif
    <!-- <p style="line-height: 1.6;">Main d'œuvre non incluse.</p> -->
</div>

{{-- ==================== FOOTER ==================== --}}
<div class="footer">
    <strong>{{ $companyName }}</strong> - {{ $companyAddress }}, {{ $companyCity }}
    - <strong>ICE:</strong> 
</div>

</body>
</html>