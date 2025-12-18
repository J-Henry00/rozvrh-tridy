<?php
include 'data.php';

// Nastavení
$user_to_fix = "admin";
$new_password = "admin123"; // Nové heslo, které se zahashuje
$target_naked_password = "admin"; // Staré nezabezpečené heslo, které hledáme

// 1. Získání aktuálního hesla z databáze
$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $user_to_fix);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_db_password = $row['password'];

    // 2. Kontrola
    if ($current_db_password === $target_naked_password) {
        // A) V DB je uloženo "admin" (nezahashované) -> PŘEPSAT NA HASH
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update_stmt->bind_param("ss", $hashed_password, $user_to_fix);
        
        if ($update_stmt->execute()) {
            echo "<h1>HOTOVO!</h1>";
            echo "Bezpečnostní riziko odstraněno.<br>";
            echo "Heslo uživatele <strong>$user_to_fix</strong> bylo v databázi uloženo jako prostý text 'admin'.<br>";
            echo "Nyní bylo změněno na bezpečný hash nového hesla<br>";
            echo "<a href='index.php'>Přejít na index</a>";
        } else {
            echo "Chyba při aktualizaci: " . $conn->error;
        }

    } else {
        // B) V DB je něco jiného (pravděpodobně už hash) -> REDIRECT NA INDEX
        header("Location: index.php");
        exit;
    }

} else {
    echo "Uživatel <strong>$user_to_fix</strong> v databázi neexistuje.";
}
?>
