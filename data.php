<?php
// data.php - Připojení k MySQL databázi

$servername = "localhost";
$username = "rozvrhac";
$password = "heslo";
$dbname = "rozvrh";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Chyba DB: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

// --- 1. NAČTENÍ ZVONĚNÍ ---
$zvoneni = [];
$sql_zvoneni = "SELECT * FROM zvoneni ORDER BY hodina ASC";
$result_zvoneni = $conn->query($sql_zvoneni);
if ($result_zvoneni->num_rows > 0) {
    while($row = $result_zvoneni->fetch_assoc()) {
        $zvoneni[$row['hodina']] = [
            'od' => date('H:i', strtotime($row['cas_od'])),
            'do' => date('H:i', strtotime($row['cas_do']))
        ];
    }
}

// --- 2. NAČTENÍ ROZVRHU ---
$rozvrh_data = [];
$sql_rozvrh = "SELECT r.*, z.cas_od, z.cas_do FROM rozvrh r JOIN zvoneni z ON r.hodina = z.hodina ORDER BY r.den, r.hodina";
$result_rozvrh = $conn->query($sql_rozvrh);
if ($result_rozvrh->num_rows > 0) {
    while($row = $result_rozvrh->fetch_assoc()) {
        $rozvrh_data[] = [
            'den' => (int)$row['den'],
            'hodina' => (int)$row['hodina'],
            'predmet' => $row['predmet'],
            'ucitel' => $row['ucitel'],
            'trida' => $row['trida'],
            'cas_od' => date('H:i', strtotime($row['cas_od'])),
            'cas_do' => date('H:i', strtotime($row['cas_do']))
        ];
    }
}

// --- 3. NAČTENÍ ZMĚN ---
$zmeny_data = [];
$sql_zmeny = "SELECT * FROM zmeny";
$result_zmeny = $conn->query($sql_zmeny);
if ($result_zmeny && $result_zmeny->num_rows > 0) {
    while($row = $result_zmeny->fetch_assoc()) {
        $zmeny_data[] = [
            'id' => $row['id'], 
            'den' => (int)$row['den'],
            'hodina' => (int)$row['hodina'],
            'typ' => $row['typ'],
            'novy_predmet' => $row['novy_predmet'],
            'novy_ucitel' => $row['novy_ucitel'],
            'novy_trida' => $row['novy_trida'],
            'poznamka' => $row['poznamka']
        ];
    }
}

// --- 4. ZKRATKY UČITELŮ ---
$zkratky_mapa = [];
$sql_ucitele = "SELECT jmeno, zkratka FROM ucitele";
$result_ucitele = $conn->query($sql_ucitele);
if ($result_ucitele && $result_ucitele->num_rows > 0) {
    while($row = $result_ucitele->fetch_assoc()) {
        $zkratky_mapa[$row['jmeno']] = $row['zkratka'];
    }
}

$dny_nazvy = ['PO', 'ÚT', 'ST', 'ČT', 'PÁ'];
?>
