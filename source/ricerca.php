<?php
session_start();
require_once 'db.php';

function isMobileDevice(): bool {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);
}

if (!isset($_SESSION["mobile"])) {
    $_SESSION["mobile"] = isMobileDevice() ? 'true' : 'false';
}

if (empty($_SESSION["user"]) || $_SESSION["user"] === 'Ospite') {
    $_SESSION["user"] = "Ospite";
}

$utente = htmlspecialchars($_SESSION["user"], ENT_QUOTES, 'UTF-8');
$titolo = $_GET["titolo"] ?? '';
$movies = $titolo !== '' ? searchMovies($titolo, $apiKey) : [];

function searchMovies(string $title, string $apiKey): array {
    $titleEncoded = urlencode($title);
    $url = "https://api.themoviedb.org/3/search/movie?api_key={$apiKey}&language=it-IT&query={$titleEncoded}&page=1&include_adult=false";
    $response = json_decode(file_get_contents($url), true);
    $totalPages = min($response['total_pages'] ?? 0, 3);

    $results = [];
    $offset = 0;

    for ($page = 1; $page <= $totalPages; $page++) {
        $results = array_merge($results, fetchPageResults($title, $page, $offset, $apiKey));
        $offset += count($results) / 3;
    }

    return $results;
}

function fetchPageResults(string $title, int $page, float $offset, string $apiKey): array {
    $titleEncoded = urlencode($title);
    $url = "https://api.themoviedb.org/3/search/movie?api_key={$apiKey}&language=it-IT&query={$titleEncoded}&page={$page}&include_adult=false";
    $response = json_decode(file_get_contents($url), true);
    $results = $response['results'] ?? [];
    $data = [];

    foreach ($results as $index => $movie) {
        $i = $offset + $index * 2;
        $data[(int)$i] = $movie['id'];
        $data[(int)$i + 1] = !empty($movie['poster_path'])
            ? "https://image.tmdb.org/t/p/w500{$movie['poster_path']}"
            : "media/nonDisponibile0.png";
    }

    return $data;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Il Mondo del Cinema</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="grey">
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="ricerca.php">Ricerca</a>
        <a href="#">About</a>
        <div class="account">
            <button id="dropbtn">
                <p id="username"><?= $utente ?></p>
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown">
                <?php if ($utente !== "Ospite"): ?>
                    <div id="logged">
                        <a href="mieiDati.php">I Miei Dati</a>
                        <a href="salvati.php">Elementi Salvati</a>
                        <a href="logout.php">Esci</a>
                    </div>
                <?php else: ?>
                    <div id="no-logged">
                        <a href="login.php">Login / Registrazione</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grey padding">
        <br>
        <div id="ricerca">
            <form method="GET" action="ricerca.php">
                <input id="ricerca" type="text" name="titolo" placeholder="Ricerca per titolo">
                <input id="ricercaS" type="submit" value="Ricerca">
            </form>
        </div>

        <br><br>

        <?php if ($titolo !== ''): ?>
            <h4>Risultati della ricerca per '<?= htmlspecialchars($titolo, ENT_QUOTES, 'UTF-8') ?>':</h4>
            <div class="risultati" style="margin-left: 65px;">
                <?php for ($i = 0; $i < count($movies); $i += 2): ?>
                    <div class="movie">
                        <a href="schedaFilm.php?id=<?= $movies[$i] ?>">
                            <img class="home" src="<?= $movies[$i + 1] ?>" alt="Poster">
                        </a>
                    </div>
                <?php endfor; ?>
            </div>
            <br><br>
        <?php endif; ?>
    </div>
</body>
</html>
