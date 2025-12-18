<?php
// cache_helper.php

/**
 * Získá data z cache nebo je načte pomocí callback funkce a uloží.
 *
 * @param string   $key       Unikátní klíč pro cache (bude název souboru).
 * @param int      $duration  Doba platnosti v sekundách (např. 1800 pro 30 min).
 * @param callable $callback  Funkce, která vrátí data, pokud cache neexistuje nebo je stará.
 * @return mixed              Data (z cache nebo z DB).
 */
function get_cached_data($key, $duration, $callback) {
    // Adresář, kam se ukládají cache soubory
    $cache_dir = __DIR__ . '/cache';
    if (!is_dir($cache_dir)) {
        if (!mkdir($cache_dir, 0777, true)) {
            // Pokud nejde vytvořit složku, vrátíme rovnou data z DB (fallback)
            return $callback();
        }
    }

    $cache_file = $cache_dir . '/' . $key . '.json';
    $current_time = time();

    // 1. Zkusíme načíst z cache
    if (file_exists($cache_file)) {
        $file_mtime = filemtime($cache_file);
        // Pokud je soubor mladší než $duration, použijeme ho
        if (($current_time - $file_mtime) < $duration) {
            $json_content = file_get_contents($cache_file);
            $data = json_decode($json_content, true);
            if ($data !== null) {
                return $data;
            }
        }
    }

    // 2. Pokud cache není nebo je stará, načteme data callbackem
    $data = $callback();

    // 3. Uložíme nová data do cache
    file_put_contents($cache_file, json_encode($data));

    return $data;
}

/**
 * Smaže celou cache (všechny .json soubory ve složce cache).
 * Volat po jakékoliv změně v DB (zmeny.php).
 */
function clear_all_cache() {
    $cache_dir = __DIR__ . '/cache';
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '/*.json');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
?>
