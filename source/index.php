<?php 
	session_start();
	require_once 'db.php';

	function isMobileDevice() {
	    $userAgent = $_SERVER['HTTP_USER_AGENT'];
	    return preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);
	}

	if (!isset($_SESSION["mobile"])) {
	    $_SESSION["mobile"] = isMobileDevice() ? 'true' : 'false';
	}

	// Imposta utente come "Ospite" se non loggato
	if (empty($_SESSION["user"]) || $_SESSION["user"] === 'Ospite') {
	    $_SESSION["user"] = "Ospite";
	}

	$utente = htmlspecialchars($_SESSION["user"], ENT_QUOTES, 'UTF-8');

	function getData($url) {
	    global $apiKey;

	    $json = file_get_contents($url);
	    if (!$json) return [];

	    $result = json_decode($json, true);
	    if (!isset($result['results'])) return [];

	    $movies = [];
	    // Prendiamo max 20 film
	    for ($i = 0; $i < 20 && isset($result['results'][$i]); $i++) {
	        $movies[] = [
	            'id' => $result['results'][$i]['id'] ?? null,
	            'poster' => $result['results'][$i]['poster_path'] ?? null,
	        ];
	    }
	    return $movies;
	}

	$link = "https://api.themoviedb.org/3/discover/movie?api_key=";
	$filter = "&language=it-IT&sort_by=vote_average.desc&include_adult=false&include_video=false&page=1&vote_count.gte=5000&with_genres=";
	$popolari = getData("{$link}{$apiKey}&language=it-IT&sort_by=popularity.desc&include_adult=false&include_video=false&page=1");
	$azione = getData("{$link}{$apiKey}{$filter}28&without_genres=16,12");
	$avventura = getData("{$link}{$apiKey}{$filter}12&without_genres=16,28");
	$fantasy = getData("{$link}{$apiKey}{$filter}14&without_genres=16");
	$fantascienza = getData("{$link}{$apiKey}{$filter}878&without_genres=16");
	$horror = getData("{$link}{$apiKey}{$filter}27&without_genres=16");
	$animazione = getData("{$link}{$apiKey}{$filter}16");
	$incassi = getData("{$link}{$apiKey}&language=it-IT&sort_by=revenue.desc&include_adult=false&include_video=false&page=1&with_runtime.lte=500");
?>

<!DOCTYPE html>
<html lang="it">
	<head>
	    <meta charset="UTF-8" />
	    <title>Il Mondo del Cinema</title>
	    <link rel="stylesheet" href="style.css" />
	    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
	</head>

	<body id="index" class="grey">
	    <?php
			// Imposta utente come "Ospite" se non loggato
			if (empty($_SESSION["user"]) || $_SESSION["user"] == 'Ospite')
				$_SESSION["user"] = "Ospite";			
			$utente = htmlspecialchars($_SESSION["user"], ENT_QUOTES, 'UTF-8');
		?>

		<div class="navbar">
		    <a href="index.php">Home</a>
		    <a href="ricerca.php">Ricerca</a>
		    <a href="">About</a>
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

	<script>
	    let scorrimento = 1700;

	    window.onload = function () {
	        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
	        if (isMobile) {
	            // Nascondi frecce e aggiusta larghezza su mobile
	            [...document.getElementsByClassName('freccia0')].forEach(el => el.style.display = 'none');
	            [...document.getElementsByClassName('freccia1')].forEach(el => el.style.display = 'none');
	            [...document.getElementsByClassName('trail')].forEach(el => el.style.width = '100%');
	            [...document.querySelectorAll('h3')].forEach(el => el.style.margin = '0');
	        } else {
	            const screenWidth = screen.width;
	            let margin, larghezza;

	            if (screenWidth === 1366) {
	                margin = 18;
	                scorrimento = 1209;
	            } else if (screenWidth === 1920) {
	                margin = 8;
	                scorrimento = 1752;
	            } else {
	                margin = 10; // default margin if screen size different
	                scorrimento = 1700;
	            }

	            const xx = screenWidth - 140 - 16;
	            larghezza = (xx * 100) / screenWidth;

	            [...document.getElementsByClassName('trail')].forEach(el => el.style.width = larghezza + '%');
	            [...document.querySelectorAll('h3')].forEach(el => el.style.width = larghezza + '%');

	            const movies = document.getElementsByClassName('movie');
	            for (let i = 0; i < movies.length; i++) {
	                movies[i].style.marginRight = margin + 'px';
	            }

	            for (let i = 19; i < movies.length; i += 20) {
	                movies[i].style.marginRight = "0";
	            }
	        }
	    };

	    function next(id) {
	        document.getElementById(id).scrollBy(scorrimento, 0);
	    }

	    function previous(id) {
	        document.getElementById(id).scrollBy(-scorrimento, 0);
	    }
	</script>

	<?php
	function createSlider(array $movies, string $sliderId) {
	    echo "<div class='scorri'>";
	    echo "<div class='freccia0' onclick='previous(\"$sliderId\")'>";
	    echo "<img src='media/arrow2.png' width='60px' alt='Freccia sinistra'>";
	    echo "</div>";

	    echo "<div class='trail' id='$sliderId'>";
	    foreach ($movies as $movie) {
	        if (!$movie['id'] || !$movie['poster']) continue;
	        $id = htmlspecialchars($movie['id']);
	        $poster = htmlspecialchars($movie['poster']);
	        echo "<div class='movie'>";
	        echo "<a href='schedaFilm.php?id=$id'>";
	        echo "<img class='home' src='https://image.tmdb.org/t/p/w500$poster' alt='Locandina film'>";
	        echo "</a><br/>";
	        echo "</div>";
	    }
	    echo "</div>";

	    echo "<div class='freccia1' onclick='next(\"$sliderId\")'>";
	    echo "<img src='media/arrow2.png' width='60px' style='transform:rotate(180deg)' alt='Freccia destra'>";
	    echo "</div>";
	    echo "</div><br>";
	}
	?>

	<div class="padding">
	    <br><br>
	    <h3>I film più popolari:</h3>
	    <?php createSlider($popolari, "popolari"); ?>

	    <h3>I migliori film Action:</h3>
	    <?php createSlider($azione, "azione"); ?>

	    <h3>I migliori film Avventura:</h3>
	    <?php createSlider($avventura, "avventura"); ?>

	    <h3>I migliori film Fantasy:</h3>
	    <?php createSlider($fantasy, "fantasy"); ?>

	    <h3>I migliori film di Fantascienza:</h3>
	    <?php createSlider($fantascienza, "fantascienza"); ?>

	    <h3>I migliori film Horror:</h3>
	    <?php createSlider($horror, "horror"); ?>

	    <h3>I migliori film d'Animazione:</h3>
	    <?php createSlider($animazione, "animazione"); ?>

	    <h3>Campioni d'Incassi:</h3>
	    <?php createSlider($incassi, "incassi"); ?>
	</div>

	</body>
</html>
