<?php

include 'data.php'; 
include 'logic.php'; // ZDE SE NAČTE $finalni_rozvrh a časy

// --- LOGIKA URL ---
$dark_mode = isset($_GET['darkMode']);

// --- 3. Filtrace pro dnešní den (Specifické pro Index, admin to nepotřebuje) ---
$dnesni_rozvrh = array_filter($finalni_rozvrh, function($item) use ($aktualni_den_v_tydnu) {
    return $item['den'] == $aktualni_den_v_tydnu;
});
usort($dnesni_rozvrh, function($a, $b) { return $a['hodina'] - $b['hodina']; });

// --- 4. Logika Dashboardu (Aktuální hodina) ---
$je_po_skole = true;
if (!empty($dnesni_rozvrh)) {
    $posledni = end($dnesni_rozvrh);
    if ($aktualni_cas <= $posledni['cas_do']) $je_po_skole = false;
}

$aktualni_hodina_data = null;
$pristi_hodina_data = null;

if (!$je_po_skole) {
    foreach ($dnesni_rozvrh as $index => $hodina) {
        if ($aktualni_cas >= $hodina['cas_od'] && $aktualni_cas <= $hodina['cas_do']) {
            $aktualni_hodina_data = $hodina;
            if (isset($dnesni_rozvrh[$index + 1])) $pristi_hodina_data = $dnesni_rozvrh[$index + 1];
            break;
        }
    }
    if (!$aktualni_hodina_data) {
        foreach ($dnesni_rozvrh as $hodina) {
            if ($aktualni_cas < $hodina['cas_od']) {
                $pristi_hodina_data = $hodina; 
                break;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Učebna praxí - Školní Informační Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" href="favicon_skolakrizik.png">
</head>
<body class="<?php echo $dark_mode ? 'dark-mode' : ''; ?>">

    <header>
    <img src="favicon_skolakrizik.png" class="logo-img" alt="Logo">
    
    <div class="header-text">
        <div class="clock-big" id="clock">00:00</div>
        <div class="date-small" id="date">...</div>
        
        <?php if($manual_time): ?>
            <?php $dn = isset($dny_nazvy[$manual_day - 1]) ? $dny_nazvy[$manual_day - 1] : "Den $manual_day"; ?>
            <div class="manual-indicator">⚙ Simulace: <?php echo $dn . " " . $manual_time; ?></div>
        <?php endif; ?>
    </div>
</header>

    <div class="slide-container">
        
        <div class="slide active" id="slide-1">
            <h2>Týdenní přehled</h2>
            <table class="grid-table">
                <thead>
                    <tr>
                        <th></th> 
                        <?php for($i=1; $i<=7; $i++): ?>
                            <th>
                                <?php echo $i . '.'; ?>
                                <?php if(isset($zvoneni[$i])): ?>
                                    <span class="header-time"><?php echo $zvoneni[$i]['od'] . '-' . $zvoneni[$i]['do']; ?></span>
                                <?php endif; ?>
                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dny_nazvy as $index => $nazev_dne): $den_cislo = $index + 1; ?>
                    <tr class="<?php echo ($den_cislo == $aktualni_den_v_tydnu) ? 'active-day-row' : ''; ?>">
                        <td class="grid-cell-day"><?php echo $nazev_dne; ?></td>
                        
                        <?php for($hod=1; $hod<=7; $hod++): 
                            $klic = $den_cislo . '-' . $hod;
                            $bunka_data = isset($finalni_rozvrh[$klic]) ? $finalni_rozvrh[$klic] : null;
                            
                            $obsah_predmet = ''; 
                            $obsah_detail = ''; 
                            $css_class = ''; 
                            $sub_info = '';
                            
                            if ($bunka_data) {
                                $obsah_predmet = $bunka_data['predmet'];
                                
                                // Získání učitele a zkratky
                                $ucitel_full = $bunka_data['ucitel'];
                                $ucitel_short = isset($zkratky_mapa[$ucitel_full]) ? $zkratky_mapa[$ucitel_full] : substr($ucitel_full, 0, 3);
                                
                                $trida_kod = $bunka_data['trida'];
                                $obsah_detail = "$trida_kod | $ucitel_short";

                                if (isset($bunka_data['zmena']) && $bunka_data['zmena']) {
                                    if ($bunka_data['typ_zmeny'] == 'cancel') {
                                        $css_class = 'cancelled';
                                        $obsah_predmet = str_replace(" (ODPADÁ)", "", $obsah_predmet); 
                                        $sub_info = '<span class="sub-info">ODPADÁ</span>';
                                    } elseif ($bunka_data['typ_zmeny'] == 'change') {
                                        $css_class = 'changed';
                                        $sub_info = '<span class="sub-info">ZMĚNA</span>';
                                    } elseif ($bunka_data['typ_zmeny'] == 'move') {
                                        $css_class = 'changed';
                                        $sub_info = '<span class="sub-info" style="color: #3498db;">PŘESUN</span>';
                                    }
                                }
                            }
                        ?>
                        
                            <td class="<?php echo $css_class; ?>">
                                <span class="grid-subject"><?php echo $obsah_predmet; ?></span>
                                <?php if($obsah_detail): ?>
                                    <span class="grid-details"><?php echo $obsah_detail; ?></span>
                                <?php endif; ?>
                                <?php echo $sub_info; ?>
                            </td>
                            
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="slide" id="slide-2">
            <h2>Dnešní rozvrh (<?php echo date("d.m."); ?>)</h2>
            <div class="daily-list">
                <?php if(empty($dnesni_rozvrh)): ?>
                    <div class="list-item"><div class="list-info">Žádné vyučování.</div></div>
                <?php else: ?>
                    <?php foreach($dnesni_rozvrh as $r): 
                        $row_class = '';
                        if($r['cas_do'] < $aktualni_cas) $row_class .= ' past';
                        if($aktualni_cas >= $r['cas_od'] && $aktualni_cas <= $r['cas_do']) $row_class .= ' current';
                        
                        $subject_class = '';
                        $display_subject = $r['predmet']; 

                        if(isset($r['zmena']) && $r['zmena']) {
                            if ($r['typ_zmeny'] == 'cancel') {
                                $subject_class = 'text-red-strike';
                                $display_subject = str_replace(" (ODPADÁ)", "", $r['predmet']);
                            } elseif ($r['typ_zmeny'] == 'change' || $r['typ_zmeny'] == 'move') {
                                $subject_class = 'text-yellow';
                            }
                        }
                    ?>
                    <div class="list-item <?php echo $row_class; ?>">
                        <div class="list-time"><?php echo $r['hodina']; ?>. hod<br><small><?php echo $r['cas_od']; ?></small></div>
                        <div class="list-info">
                            <div class="subject-name <?php echo $subject_class; ?>">
                                <?php echo $display_subject; ?>
                            </div>
                            
                            <div class="room-info">
                                <?php echo $r['trida']; ?> | <?php echo $r['ucitel']; ?>
                                <?php if(isset($r['poznamka']) && $r['poznamka']) echo " <b style='color:var(--alert-red)'>(".$r['poznamka'].")</b>"; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="slide" id="slide-3">
            <div class="status-label">Aktuální stav</div>
            <div class="current-status-box">
                <?php if($je_po_skole): ?>
                    <div class="current-subject" style="opacity: 0.6;">Konec vyučování</div>
                <?php elseif($aktualni_hodina_data): ?>
                    
                    <?php 
                        $cur_css_class = '';
                        $cur_subject_text = $aktualni_hodina_data['predmet'];
                        $is_cancelled = false; 

                        if ($aktualni_hodina_data['zmena']) {
                            if ($aktualni_hodina_data['typ_zmeny'] == 'cancel') {
                                $cur_css_class = 'text-red-strike';
                                $cur_subject_text = str_replace(" (ODPADÁ)", "", $cur_subject_text);
                                $is_cancelled = true;
                            } elseif ($aktualni_hodina_data['typ_zmeny'] == 'change' || $aktualni_hodina_data['typ_zmeny'] == 'move') {
                                $cur_css_class = 'text-yellow';
                            }
                        }
                    ?>
                    
                    <div class="current-subject <?php echo $cur_css_class; ?>">
                        <?php echo $cur_subject_text; ?>
                    </div>
                    
                    <?php if($is_cancelled): ?>
                        <div class="teacher-name text-red-strike" style="opacity: 0.7;">
                            <?php echo $aktualni_hodina_data['ucitel']; ?>
                        </div>
                        <div style="color: var(--alert-red); font-size: 2rem; font-weight: bold; margin-top:5px;">ODPADÁ</div>
                    <?php else: ?>
                        <div class="teacher-name"><?php echo $aktualni_hodina_data['ucitel']; ?></div>
                    <?php endif; ?>

                    <?php if(isset($aktualni_hodina_data['poznamka']) && $aktualni_hodina_data['poznamka'] && !$is_cancelled): ?>
                        <div style="color: var(--alert-red); font-size: 1.5rem; font-weight: bold; margin-top: 10px;">
                            <?php echo $aktualni_hodina_data['poznamka']; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="current-subject">Volno / Přestávka</div>
                <?php endif; ?>
            </div>

            <?php if(!$je_po_skole && $pristi_hodina_data): ?>
                
                <?php 
                    $next_css_class = '';
                    $next_subject_text = $pristi_hodina_data['predmet'];

                    if ($pristi_hodina_data['zmena']) {
                        if ($pristi_hodina_data['typ_zmeny'] == 'cancel') {
                            $next_css_class = 'text-red-strike';
                            $next_subject_text = str_replace(" (ODPADÁ)", "", $next_subject_text);
                        } elseif ($pristi_hodina_data['typ_zmeny'] == 'change' || $pristi_hodina_data['typ_zmeny'] == 'move') {
                            $next_css_class = 'text-yellow';
                        }
                    }
                ?>

                <div class="next-status-box">
                    <div class="status-label" style="font-size: 1rem; margin-bottom: 5px;">následuje:</div>
                    <div style="font-size: 1.5rem;">
                        <span class="<?php echo $next_css_class; ?>">
                            <?php echo $next_subject_text; ?>
                        </span> 
                        (<?php echo $pristi_hodina_data['cas_od']; ?> - <?php echo $pristi_hodina_data['trida']; ?>)
                    </div>
                    
                    <?php if($pristi_hodina_data['zmena'] && $pristi_hodina_data['typ_zmeny'] == 'cancel'): ?>
                        <div style="color: var(--alert-red); font-weight: bold;">(ODPADÁ)</div>
                    <?php endif; ?>
                </div>

            <?php elseif(!$je_po_skole && empty($pristi_hodina_data) && $aktualni_hodina_data): ?>
                 <div class="next-status-box">
                    <div style="font-size: 1.5rem;">Konec vyučování</div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="control-panel">
        <button class="switch-btn" onclick="manualSwitch()">Další pohled ⟳</button>
        
        <a href="zmeny.php<?php echo $dark_mode ? '?darkMode' : ''; ?>" style="text-decoration: none;">
            <button class="switch-btn admin">Správa změn ✎</button>
        </a>

        <?php if($beta_mode): ?>
            <button class="switch-btn beta" onclick="openTimeSettings()">
                <?php echo $manual_time ? 'Změnit simulaci ⚙' : 'Nastavit simulaci ⚙'; ?>
            </button>
            <?php if($manual_time): ?>
                <button class="switch-btn beta" onclick="stopSimulation()">Vypnout simulaci ✖</button>
            <?php endif; ?>
            <button class="switch-btn dark-toggle" onclick="toggleDarkMode()">
                <?php echo $dark_mode ? 'Light Mode ☀' : 'Dark Mode ☾'; ?>
            </button>
        <?php endif; ?>
    </div>

    <script>
        const switchInterval = 20000; 
        const manualTimeSet = "<?php echo $manual_time; ?>"; 
        const manualDaySet  = "<?php echo $manual_day; ?>";
        let autoSwitchTimer;

        // AUTOMATICKÝ REFRESH STRÁNKY (Každých 60s)
        setTimeout(function() {
            window.location.reload();
        }, 61000);

        function updateTime() {
            if (manualTimeSet !== "") {
                document.getElementById('clock').textContent = manualTimeSet;
                document.getElementById('date').textContent = "SIMULACE";
                return; 
            }
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit' });
            document.getElementById('date').textContent = now.toLocaleDateString('cs-CZ');
        }
        setInterval(updateTime, 1000); updateTime(); 

        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        function rotateSlides() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }
        function manualSwitch() {
            rotateSlides(); clearInterval(autoSwitchTimer); autoSwitchTimer = setInterval(rotateSlides, switchInterval);
        }
        autoSwitchTimer = setInterval(rotateSlides, switchInterval);

        function openTimeSettings() {
            let newTime = prompt("1. Čas simulace (HH:MM):", manualTimeSet || "09:00");
            if (newTime == null) return;
            let newDay = prompt("2. Den (1-5):", manualDaySet || "1");
            if (newDay == null) newDay = "1";
            const url = new URL(window.location.href);
            url.searchParams.set('time', newTime); url.searchParams.set('day', newDay);
            window.location.href = url.toString();
        }
        function stopSimulation() {
            const url = new URL(window.location.href);
            url.searchParams.delete('time'); url.searchParams.delete('day');
            window.location.href = url.toString();
        }
        function toggleDarkMode() {
            const url = new URL(window.location.href);
            if (url.searchParams.has('darkMode')) url.searchParams.delete('darkMode');
            else url.searchParams.set('darkMode', '');
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
