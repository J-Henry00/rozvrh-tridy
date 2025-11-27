<?php
// logic.php - Společná logika pro výpočet rozvrhu (použito v Indexu i Adminu)

// --- 1. ZÁKLADNÍ PROMĚNNÉ ---
$beta_mode = isset($_GET['betaOpen']);
$manual_time = ($beta_mode && isset($_GET['time']) && $_GET['time'] !== '') ? date('H:i', strtotime($_GET['time'])) : null;
$manual_day  = ($beta_mode && isset($_GET['day']) && $_GET['day'] !== '') ? (int)$_GET['day'] : null;

$aktualni_cas = $manual_time ? $manual_time : date('H:i');
$aktualni_den_v_tydnu = $manual_day ? $manual_day : date('N'); 
$konec_skoly_cas = '13:40'; 

// --- 2. MERGE LOGIKA (Vytvoření finálního rozvrhu) ---
$finalni_rozvrh = [];

// A) Naplnění standardním rozvrhem
foreach ($rozvrh_data as $r) {
    $klic = $r['den'] . '-' . $r['hodina'];
    $finalni_rozvrh[$klic] = $r;
    $finalni_rozvrh[$klic]['zmena'] = false;
    $finalni_rozvrh[$klic]['typ_zmeny'] = null; 
    $finalni_rozvrh[$klic]['poznamka'] = null; 
}

// B) Aplikace změn
foreach ($zmeny_data as $z) {
    $klic = $z['den'] . '-' . $z['hodina'];
    
    if (isset($finalni_rozvrh[$klic])) {
        // Editace existující hodiny
        $finalni_rozvrh[$klic]['zmena'] = true;
        $finalni_rozvrh[$klic]['typ_zmeny'] = $z['typ'];
        $finalni_rozvrh[$klic]['poznamka'] = $z['poznamka'];
        
        if ($z['typ'] == 'cancel') {
            $finalni_rozvrh[$klic]['puvodni_predmet'] = $finalni_rozvrh[$klic]['predmet'];
            $finalni_rozvrh[$klic]['predmet'] = $finalni_rozvrh[$klic]['predmet'] . " (ODPADÁ)"; 
            // Učitele nemazat, chceme ho vidět přeškrtnutého
        
        } elseif ($z['typ'] == 'change' || $z['typ'] == 'move') {
             if($z['novy_predmet']) $finalni_rozvrh[$klic]['predmet'] = $z['novy_predmet'];
             if($z['novy_ucitel'])  $finalni_rozvrh[$klic]['ucitel']  = $z['novy_ucitel'];
             if($z['novy_trida'])   $finalni_rozvrh[$klic]['trida']   = $z['novy_trida'];
        }
    } else {
        // Vytvoření nové hodiny na prázdném místě
        $start = isset($zvoneni[$z['hodina']]) ? $zvoneni[$z['hodina']]['od'] : "??:??";
        $end   = isset($zvoneni[$z['hodina']]) ? $zvoneni[$z['hodina']]['do'] : "??:??";

        $finalni_rozvrh[$klic] = [
            'den' => $z['den'],
            'hodina' => $z['hodina'],
            'predmet' => $z['novy_predmet'] ? $z['novy_predmet'] : "Nová hodina",
            'ucitel' => $z['novy_ucitel'] ? $z['novy_ucitel'] : "",
            'trida' => $z['novy_trida'] ? $z['novy_trida'] : "",
            'cas_od' => $start,
            'cas_do' => $end,
            'zmena' => true,
            'typ_zmeny' => $z['typ'],
            'poznamka' => $z['poznamka']
        ];
    }
}
?>