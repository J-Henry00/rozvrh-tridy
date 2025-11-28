<?php
include 'data.php'; 
include 'logic.php'; 
session_start();

$insert_msg = ""; $delete_msg = ""; $move_msg = ""; $perm_msg = ""; $error = "";
$dark_mode = isset($_GET['darkMode']);
$dm_link_param = $dark_mode ? '?darkMode' : '';
$dm_form_action = $dark_mode ? '?darkMode' : '';

// --- AJAX HANDLER (DRAG & DROP) ---
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'move') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { echo json_encode(['status'=>'error','message'=>'Auth error']); exit; }
    
    $fd=(int)$_POST['from_den']; $fh=(int)$_POST['from_hod']; $td=(int)$_POST['to_den']; $th=(int)$_POST['to_hod'];
    
    // Zkusíme najít hodinu v rozvrhu
    $chk=$conn->query("SELECT predmet,ucitel,trida FROM rozvrh WHERE den=$fd AND hodina=$fh");
    if($chk->num_rows>0){
        $orig=$chk->fetch_assoc();
        $op=$conn->real_escape_string($orig['predmet']); $ou=$conn->real_escape_string($orig['ucitel']); $ot=$conn->real_escape_string($orig['trida']);
        
        $conn->query("INSERT INTO zmeny (den,hodina,typ,poznamka) VALUES ($fd,$fh,'cancel','D&D Přesun')");
        if($conn->query("INSERT INTO zmeny (den,hodina,typ,novy_predmet,novy_ucitel,novy_trida,poznamka) VALUES ($td,$th,'move','$op','$ou','$ot','D&D Přesun')")) {
            echo json_encode(['status'=>'success']);
        } else {
            echo json_encode(['status'=>'error','message'=>$conn->error]);
        }
    } else {
        echo json_encode(['status'=>'error','message'=>'Nelze přesunout prázdné pole nebo dočasnou hodinu.']);
    }
    exit;
}

// --- STANDARD LOGIC ---
if(isset($_POST['login_username'])){ 
    $u=$conn->real_escape_string($_POST['login_username']); $p=$_POST['login_password'];
    $res=$conn->query("SELECT * FROM users WHERE username='$u'");
    if($res->num_rows>0){
        $row=$res->fetch_assoc();
        if(password_verify($p,$row['password'])){$_SESSION['logged_in']=true;$_SESSION['user_name']=$u;}else{$error="Špatné heslo!";}
    }else{$error="Uživatel neexistuje";}
}
if(isset($_GET['logout'])){session_destroy();header("Location: zmeny.php".$dm_link_param);exit;}
if(isset($_GET['delete_id'])){$conn->query("DELETE FROM zmeny WHERE id=".(int)$_GET['delete_id']);header("Location: zmeny.php".$dm_link_param);exit;}
if(isset($_POST['submit_change'])){
    $d=(int)$_POST['den'];$h=(int)$_POST['hodina'];$t=$_POST['typ'];$p=$conn->real_escape_string($_POST['poznamka']);
    if($conn->query("INSERT INTO zmeny (den,hodina,typ,poznamka) VALUES ($d,$h,'$t','$p')")) header("Refresh:0");
}
if(isset($_POST['submit_move'])){
    $fd=(int)$_POST['from_den'];$fh=(int)$_POST['from_hod'];$td=(int)$_POST['to_den'];$th=(int)$_POST['to_hod'];
    $chk=$conn->query("SELECT predmet,ucitel,trida FROM rozvrh WHERE den=$fd AND hodina=$fh");
    if($chk->num_rows>0){
        $row=$chk->fetch_assoc();
        $op=$conn->real_escape_string($row['predmet']);$ou=$conn->real_escape_string($row['ucitel']);$ot=$conn->real_escape_string($row['trida']);
        $conn->query("INSERT INTO zmeny (den,hodina,typ,poznamka) VALUES ($fd,$fh,'cancel','Přesunuto')");
        $conn->query("INSERT INTO zmeny (den,hodina,typ,novy_predmet,novy_ucitel,novy_trida,poznamka) VALUES ($td,$th,'move','$op','$ou','$ot','Přesun')");
        header("Refresh:0");
    }
}
// PERMANENT SAVE/DELETE
if (isset($_POST['submit_perm_save'])) {
    $den = (int)$_POST['modal_den']; $hod = (int)$_POST['modal_hod'];
    $predmet = $conn->real_escape_string($_POST['modal_predmet']);
    $ucitel = $conn->real_escape_string($_POST['modal_ucitel']);
    $trida = $conn->real_escape_string($_POST['modal_trida']);
    $conn->query("REPLACE INTO rozvrh (den, hodina, predmet, ucitel, trida) VALUES ($den, $hod, '$predmet', '$ucitel', '$trida')");
    $conn->query("INSERT IGNORE INTO ucitele (jmeno, zkratka) VALUES ('$ucitel', SUBSTRING('$ucitel', 1, 3))");
    echo "<script>window.location.href='zmeny.php".$dm_link_param."';</script>";
}
if (isset($_POST['submit_perm_delete'])) {
    $den = (int)$_POST['modal_den']; $hod = (int)$_POST['modal_hod'];
    $conn->query("DELETE FROM rozvrh WHERE den = $den AND hodina = $hod");
    echo "<script>window.location.href='zmeny.php".$dm_link_param."';</script>";
}

$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Správa změn</title>
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" href="favicon_skolakrizik.png">
</head>
<body class="<?php echo $dark_mode ? 'dark-mode' : ''; ?>">

    <header>
        <img src="favicon_skolakrizik.png" class="logo-img" alt="Logo">
        <div class="header-text">
            <h1 style="margin: 0; color: var(--accent-blue);">Administrace rozvrhu</h1>
            <div style="margin-top: 5px;">
                <a href="index.php<?php echo $dm_link_param; ?>" style="color: var(--text-color); text-decoration: underline;">zpět na tabuli</a>
            </div>
        </div>
    </header>

<?php if (!$is_logged_in): ?>
    <div class="login-box">
        <h2>Přihlášení</h2>
        <?php if($error) echo "<p style='color:red'>$error</p>"; ?>
        <form method="post" action="zmeny.php<?php echo $dm_form_action; ?>">
            <input type="text" name="login_username" placeholder="Jméno" required>
            <input type="password" name="login_password" placeholder="Heslo" required>
            <button type="submit">Přihlásit se</button>
        </form>
    </div>
<?php else: ?>

    <div class="admin-container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <span>Uživatel: <strong><?php echo $_SESSION['user_name']; ?></strong> <a href="register.php<?php echo $dm_link_param; ?>" style="margin-left: 15px; color: var(--accent-blue); text-decoration: none; font-size: 0.9rem;">[ + Přidat správce ]</a></span>
            <a href="?logout<?php echo $dark_mode ? '&darkMode' : ''; ?>" class="btn-delete" style="text-decoration:none; padding:8px 15px; font-size:1rem;">Odhlásit</a>
        </div>

        <div class="admin-cols">
            <div class="admin-col">
                <h3>Nahlásit Supl / Odpadnutí</h3>
                <form method="post" action="zmeny.php<?php echo $dm_form_action; ?>">
                    <div style="display:flex; gap:5px;">
                        <select name="den"><option value="1">Po</option><option value="2">Út</option><option value="3">St</option><option value="4">Čt</option><option value="5">Pá</option></select>
                        <select name="hodina"><?php for($i=1; $i<=7; $i++) echo "<option value='$i'>$i. hod</option>"; ?></select>
                    </div>
                    <select name="typ"><option value="cancel">Odpadá</option><option value="change">Suplování</option></select>
                    <input type="text" name="poznamka" placeholder="Poznámka">
                    <button type="submit" name="submit_change">Uložit změnu</button>
                </form>
            </div>
            <div class="admin-col" style="border-color:var(--accent-blue);">
                <h3 style="color:var(--accent-blue);">Přesunout hodinu</h3>
                <form method="post" action="zmeny.php<?php echo $dm_form_action; ?>">
                    <div style="display:flex; gap:10px;">
                        <div style="flex:1"><label>ODKUD</label><div style="display:flex; gap:5px;"><select name="from_den"><option value="1">Po</option><option value="2">Út</option><option value="3">St</option><option value="4">Čt</option><option value="5">Pá</option></select><select name="from_hod"><?php for($i=1; $i<=7; $i++) echo "<option value='$i'>$i</option>"; ?></select></div></div>
                        <div style="flex:1"><label>KAM</label><div style="display:flex; gap:5px;"><select name="to_den"><option value="1">Po</option><option value="2">Út</option><option value="3">St</option><option value="4">Čt</option><option value="5">Pá</option></select><select name="to_hod"><?php for($i=1; $i<=7; $i++) echo "<option value='$i'>$i</option>"; ?></select></div></div>
                    </div>
                    <button type="submit" name="submit_move" style="background:var(--accent-blue);">Provést přesun</button>
                </form>
            </div>
        </div>

        <hr style="margin:30px 0; border-color:var(--border-color);">
        <h3>Aktivní změny</h3>
        <?php if(empty($zmeny_data)): ?><p style="opacity:0.6;">Žádné aktivní změny.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Kdy</th><th>Typ</th><th>Info</th><th>Akce</th></tr></thead>
                <tbody>
                    <?php foreach($zmeny_data as $z): 
                        $dn=isset($dny_nazvy[$z['den']-1])?$dny_nazvy[$z['den']-1]:$z['den'];
                        $lbl=match($z['typ']){'cancel'=>'<span style="color:var(--alert-red)">Odpadá</span>','change'=>'<span style="color:#f1c40f">Supl</span>','move'=>'<span style="color:#3498db">Přesun</span>',default=>$z['typ']};
                    ?>
                    <tr>
                        <td><?php echo $dn." / ".$z['hodina'].". hod"; ?></td>
                        <td><?php echo $lbl; ?></td>
                        <td><?php echo $z['poznamka']; ?></td>
                        <td style="text-align:center;"><a href="?delete_id=<?php echo $z['id'].($dark_mode?'&darkMode':''); ?>" class="btn-delete">Smazat</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <hr style="margin:30px 0; border-color:var(--border-color);">
        <h3 style="color:var(--accent-blue);">Interaktivní rozvrh (Drag & Drop + Edit)</h3>
        <p style="font-size:0.9rem; color:gray; margin-bottom:10px;">Chytni pro přesun (dočasné), klikni na <span style="font-size:1.2rem;">✏️</span> pro úpravu (trvalé).</p>
        
        <div class="admin-preview-wrapper">
            <table class="grid-table">
                <thead><tr><th></th><?php for($i=1;$i<=7;$i++) echo "<th>$i.</th>"; ?></tr></thead>
                <tbody>
                    <?php foreach($dny_nazvy as $index => $nazev_dne): $den_cislo = $index + 1; ?>
                    <tr>
                        <td class="grid-cell-day"><?php echo $nazev_dne; ?></td>
                        <?php for($hod=1; $hod<=7; $hod++): 
                            $klic = $den_cislo . '-' . $hod;
                            $bunka_data = isset($finalni_rozvrh[$klic]) ? $finalni_rozvrh[$klic] : null;
                            $drag=false; $op=''; $od=''; $cls=''; $orig_ucitel=''; $orig_predmet=''; $orig_trida='';
                            
                            // Pro modal potřebujeme PŮVODNÍ DATA z DB (ne ovlivněná změnou)
                            // Protože nemáme snadný přístup k raw rozvrhu zde (je už mergnutý),
                            // použijeme to co vidíme, ale uživatel si to případně přepíše.
                            // Správnější by bylo načíst $rozvrh_data zvlášť, ale pro jednoduchost stačí toto.
                            
                            if ($bunka_data) {
                                $drag=true;
                                if(isset($bunka_data['zmena']) && $bunka_data['typ_zmeny'] == 'cancel') $drag=false;
                                $op=$bunka_data['predmet'];
                                $uf=$bunka_data['ucitel'];
                                $us=isset($zkratky_mapa[$uf])?$zkratky_mapa[$uf]:substr($uf,0,3);
                                $tk=$bunka_data['trida'];
                                $od="$tk | $us";
                                
                                $orig_predmet = htmlspecialchars($op, ENT_QUOTES);
                                $orig_ucitel = htmlspecialchars($uf, ENT_QUOTES);
                                $orig_trida = htmlspecialchars($tk, ENT_QUOTES);

                                if(isset($bunka_data['zmena'])){
                                    if($bunka_data['typ_zmeny']=='cancel'){$cls='cancelled'; $op=str_replace(" (ODPADÁ)","",$op);}
                                    else $cls='changed';
                                }
                            }
                        ?>
                        <td class="drop-zone <?php echo $cls; ?>" data-den="<?php echo $den_cislo; ?>" data-hodina="<?php echo $hod; ?>">
                            <button type="button" class="cell-edit-btn" onclick="openModal(<?php echo $den_cislo; ?>, <?php echo $hod; ?>, '<?php echo $orig_predmet; ?>', '<?php echo $orig_ucitel; ?>', '<?php echo $orig_trida; ?>')">✏️</button>
                            
                            <?php if($bunka_data): ?>
                                <div class="draggable-content <?php echo $drag?'draggable-cell':''; ?>" draggable="<?php echo $drag?'true':'false'; ?>" data-den="<?php echo $den_cislo; ?>" data-hodina="<?php echo $hod; ?>">
                                    <span class="grid-subject"><?php echo $op; ?></span>
                                    <span class="grid-details"><?php echo $od; ?></span>
                                </div>
                            <?php else: ?>
                                <div style="min-height:50px;"></div>
                            <?php endif; ?>
                        </td>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
<?php endif; ?>

<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h3 style="color:var(--perm-green);">Upravit trvalý rozvrh</h3>
        <p id="modal-info" style="margin-bottom:15px; font-weight:bold;"></p>
        
        <form method="post" action="zmeny.php<?php echo $dm_form_action; ?>">
            <input type="hidden" name="modal_den" id="modal_den">
            <input type="hidden" name="modal_hod" id="modal_hod">
            
            <label>Předmět:</label>
            <input type="text" name="modal_predmet" id="modal_predmet" required>
            
            <label>Učitel:</label>
            <input list="teachers_list" name="modal_ucitel" id="modal_ucitel" required>
            <datalist id="teachers_list">
                <?php foreach(array_keys($zkratky_mapa) as $tn): ?>
                    <option value="<?php echo $tn; ?>">
                <?php endforeach; ?>
            </datalist>
            
            <label>Třída / Místnost:</label>
            <input type="text" name="modal_trida" id="modal_trida" required>
            
            <button type="submit" name="submit_perm_save" class="btn-perm-save">💾 Uložit / Přepsat trvale</button>
            <button type="submit" name="submit_perm_delete" class="btn-perm-del" onclick="return confirm('Opravdu smazat tuto hodinu navždy?')">🗑 Smazat trvale</button>
        </form>
    </div>
</div>

<script>
    // MODAL LOGIC
    const modal = document.getElementById('editModal');
    function openModal(d, h, p, u, t) {
        document.getElementById('modal_den').value = d;
        document.getElementById('modal_hod').value = h;
        document.getElementById('modal_predmet').value = p || '';
        document.getElementById('modal_ucitel').value = u || '';
        document.getElementById('modal_trida').value = t || '';
        const days = ['Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek'];
        document.getElementById('modal-info').innerText = days[d-1] + ', ' + h + '. hodina';
        modal.style.display = 'flex';
    }
    function closeModal() { modal.style.display = 'none'; }
    window.onclick = function(e) { if (e.target == modal) closeModal(); }

    // DRAG AND DROP LOGIC
    document.addEventListener('DOMContentLoaded', () => {
        const draggables = document.querySelectorAll('.draggable-cell');
        const dropZones = document.querySelectorAll('.drop-zone');

        draggables.forEach(draggable => {
            draggable.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('from_den', draggable.dataset.den);
                e.dataTransfer.setData('from_hodina', draggable.dataset.hodina);
                draggable.style.opacity = '0.4';
            });
            draggable.addEventListener('dragend', () => { draggable.style.opacity = '1'; });
        });

        dropZones.forEach(zone => {
            zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('drag-over'); });
            zone.addEventListener('dragleave', () => { zone.classList.remove('drag-over'); });
            
            zone.addEventListener('drop', (e) => {
                e.preventDefault(); zone.classList.remove('drag-over');
                const fd = e.dataTransfer.getData('from_den');
                const fh = e.dataTransfer.getData('from_hodina');
                const td = zone.dataset.den;
                const th = zone.dataset.hodina;

                if (!fd || !fh) return;
                if (fd === td && fh === th) return;

                if (confirm(`Dočasně přesunout hodinu?`)) {
                    const formData = new FormData();
                    formData.append('ajax_action', 'move');
                    formData.append('from_den', fd);
                    formData.append('from_hod', fh);
                    formData.append('to_den', td);
                    formData.append('to_hod', th);

                    fetch('zmeny.php<?php echo $dm_link_param; ?>', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') location.reload();
                        else alert('Chyba: ' + data.message);
                    })
                    .catch(e => alert('Chyba komunikace.'));
                }
            });
        });
    });
</script>
</body>
</html>
