<?php
session_start();
require_once 'db.php';

if (empty($_SESSION["user"]) || $_SESSION["user"] === 'Ospite') {
    $_SESSION["user"] = "Ospite";
}

$IDfilm = $_GET["IDfilm"] ?? '';
$titolo = $_GET["titolo"] ?? '';
$percorso = $_GET["percorso"] ?? '';
$fav = $_GET["fav"] ?? '';

if ($_SESSION["user"] === "Ospite") {
    // alert e redirect via JS
    echo "<script>
        alert('Devi essere un utente registrato per salvare un film');
        window.location.href = 'schedaFilm.php?id=" . addslashes($IDfilm) . "';
    </script>";
    exit; // non continua con il resto
} else {
    if ($fav === 'false') {
        // Inserisci film (usa INSERT IGNORE per MySQL oppure gestisci errore)
        $insertFilm = "INSERT IGNORE INTO film (IDfilm, percorso, titolo) VALUES (:IDfilm, :percorso, :titolo)";
        $stmtFilm = $connection->prepare($insertFilm);
        $stmtFilm->execute([
            ':IDfilm' => $IDfilm,
            ':percorso' => $percorso,
            ':titolo' => $titolo
        ]);

        // Inserisci preferenza
        $insertPref = "INSERT INTO preferisce (IDfilm, username) VALUES (:IDfilm, :username)";
        $stmtPref = $connection->prepare($insertPref);
        $stmtPref->execute([
            ':IDfilm' => $IDfilm,
            ':username' => $_SESSION["user"]
        ]);
    } else {
        // Cancella preferenza
        $deletePref = "DELETE FROM preferisce WHERE IDfilm = :IDfilm AND username = :username";
        $stmtDel = $connection->prepare($deletePref);
        $stmtDel->execute([
            ':IDfilm' => $IDfilm,
            ':username' => $_SESSION["user"]
        ]);
    }
}

header("Location: schedaFilm.php?id=" . urlencode($IDfilm));
exit;
?>
