<?php
// data.php - Databáze uživatelů, rozvrhu a změn

// 1. UŽIVATELÉ (Jméno => Heslo)
$users = [
    'admin'    => 'admin123',
    'novak'    => 'novak1',
    'vesela'   => 'vesela2',
    'reditel'  => 'sefe'
];

// 2. TABULKA ROZVRH (Stálá data)
$rozvrh_data = [
    // PONDĚLÍ
    ['den' => 1, 'hodina' => 1, 'predmet' => 'Matematika', 'ucitel' => 'Mgr. Přísná', 'trida' => '1A', 'cas_od' => '07:30', 'cas_do' => '08:15'],
    ['den' => 1, 'hodina' => 2, 'predmet' => 'Fyzika', 'ucitel' => 'Ing. Newton', 'trida' => 'LabF', 'cas_od' => '08:25', 'cas_do' => '09:10'],
    ['den' => 1, 'hodina' => 3, 'predmet' => 'Programování', 'ucitel' => 'Mgr. Novák', 'trida' => 'PC1', 'cas_od' => '09:15', 'cas_do' => '10:00'],
    ['den' => 1, 'hodina' => 4, 'predmet' => 'Programování', 'ucitel' => 'Mgr. Novák', 'trida' => 'PC1', 'cas_od' => '10:20', 'cas_do' => '11:05'],
    ['den' => 1, 'hodina' => 5, 'predmet' => 'Angličtina', 'ucitel' => 'Bc. Smith', 'trida' => 'Jaz2', 'cas_od' => '11:10', 'cas_do' => '11:55'],
    ['den' => 1, 'hodina' => 6, 'predmet' => 'Tělocvik', 'ucitel' => 'Mgr. Svalnatý', 'trida' => 'Tělocvična', 'cas_od' => '12:05', 'cas_do' => '12:50'],
    ['den' => 1, 'hodina' => 7, 'predmet' => 'Občanská nauka', 'ucitel' => 'Mgr. Moudrý', 'trida' => '4', 'cas_od' => '12:55', 'cas_do' => '13:40'],

    // ÚTERÝ
    ['den' => 2, 'hodina' => 3, 'predmet' => 'Databáze', 'ucitel' => 'Ing. Datař', 'trida' => 'PC2', 'cas_od' => '09:15', 'cas_do' => '10:00'],
    ['den' => 2, 'hodina' => 4, 'predmet' => 'Databáze', 'ucitel' => 'Ing. Datař', 'trida' => 'PC2', 'cas_od' => '10:20', 'cas_do' => '11:05'],
    ['den' => 2, 'hodina' => 5, 'predmet' => 'Český jazyk', 'ucitel' => 'Mgr. Veselá', 'trida' => '4', 'cas_od' => '11:10', 'cas_do' => '11:55'],
    ['den' => 2, 'hodina' => 6, 'predmet' => 'Dějepis', 'ucitel' => 'PhDr. Starý', 'trida' => '2B', 'cas_od' => '12:05', 'cas_do' => '12:50'],
    ['den' => 2, 'hodina' => 7, 'predmet' => 'Zeměpis', 'ucitel' => 'Mgr. Globus', 'trida' => 'Zem1', 'cas_od' => '12:55', 'cas_do' => '13:40'],

    // STŘEDA
    ['den' => 3, 'hodina' => 1, 'predmet' => 'Praxe', 'ucitel' => 'Mistr Kutil', 'trida' => 'Dílna', 'cas_od' => '07:30', 'cas_do' => '08:15'],
    ['den' => 3, 'hodina' => 2, 'predmet' => 'Praxe', 'ucitel' => 'Mistr Kutil', 'trida' => 'Dílna', 'cas_od' => '08:25', 'cas_do' => '09:10'],
    ['den' => 3, 'hodina' => 5, 'predmet' => 'Webové App', 'ucitel' => 'Ing. Script', 'trida' => 'PC1', 'cas_od' => '11:10', 'cas_do' => '11:55'],
    ['den' => 3, 'hodina' => 6, 'predmet' => 'Webové App', 'ucitel' => 'Ing. Script', 'trida' => 'PC1', 'cas_od' => '12:05', 'cas_do' => '12:50'],
    ['den' => 3, 'hodina' => 7, 'predmet' => 'Ekonomika', 'ucitel' => 'Ing. Bohatý', 'trida' => '4', 'cas_od' => '12:55', 'cas_do' => '13:40'],

    // ČTVRTEK
    ['den' => 4, 'hodina' => 1, 'predmet' => 'Programování', 'ucitel' => 'Mgr. Novák', 'trida' => '3C', 'cas_od' => '07:30', 'cas_do' => '08:15'],
    ['den' => 4, 'hodina' => 2, 'predmet' => 'Programování', 'ucitel' => 'Mgr. Novák', 'trida' => '3C', 'cas_od' => '08:25', 'cas_do' => '09:10'],
    ['den' => 4, 'hodina' => 3, 'predmet' => 'CAD systémy', 'ucitel' => 'Ing. Dvořák', 'trida' => 'V3', 'cas_od' => '09:15', 'cas_do' => '10:00'],
    ['den' => 4, 'hodina' => 4, 'predmet' => 'CAD systémy', 'ucitel' => 'Ing. Dvořák', 'trida' => 'V3', 'cas_od' => '10:20', 'cas_do' => '11:05'],
    ['den' => 4, 'hodina' => 5, 'predmet' => 'Elektronika', 'ucitel' => 'Ing. Svoboda', 'trida' => '4', 'cas_od' => '11:10', 'cas_do' => '11:55'],
    ['den' => 4, 'hodina' => 6, 'predmet' => 'Český jazyk', 'ucitel' => 'Mgr. Veselá', 'trida' => '4', 'cas_od' => '12:05', 'cas_do' => '12:50'],
    ['den' => 4, 'hodina' => 7, 'predmet' => 'ČJ cvičení', 'ucitel' => 'Mgr. Veselá', 'trida' => '4', 'cas_od' => '12:55', 'cas_do' => '13:40'],

    // PÁTEK
    ['den' => 5, 'hodina' => 1, 'predmet' => 'Matematika', 'ucitel' => 'Dr. Černý', 'trida' => '1A', 'cas_od' => '07:30', 'cas_do' => '08:15'],
    ['den' => 5, 'hodina' => 2, 'predmet' => 'Angličtina', 'ucitel' => 'Bc. Smith', 'trida' => 'Jaz1', 'cas_od' => '08:25', 'cas_do' => '09:10'],
    ['den' => 5, 'hodina' => 3, 'predmet' => 'Chemie', 'ucitel' => 'Ing. Zkumavka', 'trida' => 'LabCh', 'cas_od' => '09:15', 'cas_do' => '10:00'],
    ['den' => 5, 'hodina' => 4, 'predmet' => 'Biologie', 'ucitel' => 'Mgr. Přírodopis', 'trida' => 'Bio', 'cas_od' => '10:20', 'cas_do' => '11:05'],
    ['den' => 5, 'hodina' => 5, 'predmet' => 'Třídnická hod', 'ucitel' => 'Mgr. Novák', 'trida' => '3C', 'cas_od' => '11:10', 'cas_do' => '11:55'],
];

// 3. TABULKA ZMĚNY (Data pro tento týden)
$zmeny_data = [
    // Pátek 1. hodina ODPADÁ
    ['den' => 5, 'hodina' => 1, 'typ' => 'cancel', 'poznamka' => 'Odpadá'],
    // Pátek 2. hodina ZMĚNA
    ['den' => 5, 'hodina' => 2, 'typ' => 'change', 'novy_predmet' => 'Informatika', 'novy_ucitel' => 'Ing. IT', 'poznamka' => 'Suplování'],
];

// Zvonění
$zvoneni = [
    1 => ['od' => '07:30', 'do' => '08:15'],
    2 => ['od' => '08:25', 'do' => '09:10'],
    3 => ['od' => '09:15', 'do' => '10:00'],
    4 => ['od' => '10:20', 'do' => '11:05'],
    5 => ['od' => '11:10', 'do' => '11:55'],
    6 => ['od' => '12:05', 'do' => '12:50'],
    7 => ['od' => '12:55', 'do' => '13:40'],
];

$dny_nazvy = ['PO', 'ÚT', 'ST', 'ČT', 'PÁ'];
?>