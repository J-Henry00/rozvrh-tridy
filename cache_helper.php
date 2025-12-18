<?php
// cache_helper.php

/**
 * Získá data z cache (cookies) nebo je načte pomocí callback funkce a uloží do cookies.
 *
 * @param string   $key       Unikátní klíč pro cache.
 * @param int      $duration  Doba platnosti v sekundách.
 * @param callable $callback  Funkce, která vrátí data, pokud cache neexistuje.
 * @return mixed              Data.
 */
function get_cached_data($key, $duration, $callback) {
    $cookie_name = 'cache_' . $key;

    // 1. Zkusíme načíst z cookies
    if (isset($_COOKIE[$cookie_name])) {
        // Data jsou v cookies zakódována Base64 (aby přežila transport)
        $json = base64_decode($_COOKIE[$cookie_name]);
        $data = json_decode($json, true);
        
        if ($data !== null) {
            return $data;
        }
    }

    // 2. Pokud cache není, načteme data callbackem (DB)
    $data = $callback();

    // 3. Uložíme do cookies
    $json = json_encode($data);
    $base64_data = base64_encode($json); // Base64 zvětší velikost o 33%

    // Ochrana proti přetečení limitu cookies (cca 4096 bytů)
    // Necháváme rezervu, takže limit cca 3800 pro bezpečí
    if (strlen($base64_data) < 3800) {
        // Nastavíme cookie
        // setcookie(name, value, expires, path, domain, secure, httponly)
        if (!headers_sent()) {
            setcookie($cookie_name, $base64_data, time() + $duration, "/", "", false, true);
        }
    } else {
        // Data jsou příliš velká pro cookie -> nelze cachovat u klienta tímto způsobem.
        // Vracíme data, ale neukládáme.
    }

    return $data;
}

/**
 * Smaže celou cache (všechny cookies začínající na cache_).
 */
function clear_all_cache() {
    if (isset($_COOKIE)) {
        foreach ($_COOKIE as $key => $val) {
            if (strpos($key, 'cache_') === 0) {
                // Smazání nastavením expirace do minulosti
                setcookie($key, "", time() - 3600, "/");
                // Odstranění z globálního pole pro tento běh skriptu
                unset($_COOKIE[$key]);
            }
        }
    }
}