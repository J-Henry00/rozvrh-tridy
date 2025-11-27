<?php
include 'data.php';
session_start();

// --- ZABEZPEČENÍ: POUZE PRO PŘIHLÁŠENÉ ---
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Pokud není přihlášen, vykopneme ho na přihlášení
    header("Location: zmeny.php");
    exit;
}

// --- DARK MODE ---
$dark_mode = isset($_GET['darkMode']);
$dm_link_param = $dark_mode ? '?darkMode' : '';
$dm_form_action = $dark_mode ? '?darkMode' : '';

$msg = "";

// --- LOGIKA REGISTRACE ---
if (isset($_POST['register'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];
    
    // 1. Zkontrolujeme, zda uživatel už neexistuje
    $check = $conn->query("SELECT id FROM users WHERE username = '$user'");
    
    if ($check->num_rows > 0) {
        $msg = "<p style='color:var(--alert-red)'>Uživatel <strong>$user</strong> už existuje.</p>";
    } else {
        // 2. Zahashujeme heslo a uložíme
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password) VALUES ('$user', '$hashed_pass')";
        
        if ($conn->query($sql) === TRUE) {
            $msg = "<p style='color:green'>Správce <strong>$user</strong> byl úspěšně vytvořen!</p>";
        } else {
            $msg = "<p style='color:var(--alert-red)'>Chyba DB: " . $conn->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Registrace nového správce</title>
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" href="favicon_skolakrizik.png">
</head>
<body class="<?php echo $dark_mode ? 'dark-mode' : ''; ?>">

    <header>
        <img src="favicon_skolakrizik.png" class="logo-img" alt="Logo">
        <div class="header-text">
            <h1 style="margin: 0; line-height: 1; color: var(--accent-blue);">Registrace správce</h1>
        </div>
    </header>

    <div class="login-box">
        <h2>Vytvořit nový účet</h2>
        
        <?php echo $msg; ?>
        
        <form method="post" action="register.php<?php echo $dm_form_action; ?>">
            <input type="text" name="username" placeholder="Uživatelské jméno" required autocomplete="off">
            <input type="password" name="password" placeholder="Heslo" required autocomplete="new-password">
            <button type="submit" name="register">Vytvořit účet</button>
        </form>
        
        <p style="margin-top: 20px;">
            <a href="zmeny.php<?php echo $dm_link_param; ?>">Zpět do správy změn</a>
        </p>
    </div>

</body>
</html>
