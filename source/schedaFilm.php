<?php session_start();
require_once 'db.php';?>

<html>
<head>
	<title>Il Mondo del Cinema </title>
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="font.css">	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body onload='change()' onresize='size()'> 
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

	<?php

		function fetchTMDB($endpoint, $apiKey, $lang = null) {
		    $url = "https://api.themoviedb.org/3/movie/{$endpoint}?api_key={$apiKey}";
		    if ($lang) $url .= "&language={$lang}";
		    $json = @file_get_contents($url);
		    return $json ? json_decode($json, true) : null;
		}

		function getCrewByJob($movieID, $apiKey, $jobTitle) {
		    $credits = fetchTMDB("{$movieID}/credits", $apiKey);
		    if (!$credits || !isset($credits['crew'])) return ["Non disponibile"];

		    $result = [];
		    foreach ($credits['crew'] as $person) 
		        if (isset($person['job']) && $person['job'] === $jobTitle && isset($person['name'])) 
		            $result[] = $person['name'];		        	    

	    	return !empty($result) ? $result : ["Non disponibile"];
		}

		function getCast($movieID, $apiKey) {
		    $credits = fetchTMDB("{$movieID}/credits", $apiKey, "it-IT");
		    if (!$credits || !isset($credits['cast'])) 
		    	return ["media/nonDisponibile.png"];

		    $cast = [];
		    foreach (array_slice($credits['cast'], 0, 30) as $actor) {
		        $character = $actor['character'] ?? "Sconosciuto";
		        $name = $actor['name'] ?? "Sconosciuto";
		        $photo = isset($actor['profile_path']) 
		                 ? "https://image.tmdb.org/t/p/original" . $actor['profile_path']
		                 : "media/nonDisponibile.png";
		        $cast[] = $character;
		        $cast[] = $name;
		        $cast[] = $photo;
		    }

		    return $cast;
		}

		function getGenres($movieID, $apiKey) {
		    $data = fetchTMDB($movieID, $apiKey, "it-IT");
		    if (!$data || empty($data['genres'])) return ["Non disponibile"];
		    return array_column($data['genres'], 'name');
		}

		function getProduction($movieID, $apiKey) {
		    $data = fetchTMDB($movieID, $apiKey);
		    if (!$data || empty($data['production_companies'])) return ["Non disponibile"];
		    return array_filter(array_column($data['production_companies'], 'name'));
		}

		function getMovieData($movieID, $apiKey) {
		    $it = fetchTMDB($movieID, $apiKey, "it-IT");
		    $en = fetchTMDB($movieID, $apiKey);

		    $get = function($key) use ($it, $en) {
		        return $it[$key] ?? $en[$key] ?? "Non disponibile";
		    };

		    return [
		        'title'             => $get('title'),
		        'original_title'    => $get('original_title'),
		        'id'                => $get('id'),
		        'original_language' => $get('original_language'),
		        'png'               => $get('poster_path'),
		        'runtime'           => $get('runtime'),
		        'overview'          => $get('overview'),
		        'release'           => $get('release_date'),
		        'status'            => $get('status'),
		        'budget'            => $get('budget') ?: "Non disponibile",
		        'revenue'           => $get('revenue') ?: "Non disponibile",
		        'tagline'           => $get('tagline'),
		        'vote_average'      => $get('vote_average')
		    ];
		}

		function getVideos($movieID, $apiKey) {
		    $results = [];
		    $languages = ["it-IT", null]; // IT first, fallback EN

		    foreach ($languages as $lang) {
		        $videos = fetchTMDB("{$movieID}/videos", $apiKey, $lang)['results'] ?? [];
		        foreach ($videos as $video) {
		            if (!empty($video['key']) && strlen($video['key']) === 11) {
		                $results[] = $video['key'];
		            }
		        }
		    }

		    return array_unique($results);
		}

		function getData($movieID, $apiKey)
		{
		    $urlIT = "https://api.themoviedb.org/3/movie/{$movieID}?api_key={$apiKey}&language=it-IT";
		    $urlEN = "https://api.themoviedb.org/3/movie/{$movieID}?api_key={$apiKey}&language=en-US";

		    $dataIT = json_decode(@file_get_contents($urlIT), true) ?: [];
		    $dataEN = json_decode(@file_get_contents($urlEN), true) ?: [];

		    return [
		        'title' => $dataIT['title'] ?? $dataEN['title'] ?? 'Non disponibile',
		        'original_title' => $dataIT['original_title'] ?? $dataEN['original_title'] ?? 'Non disponibile',
		        'id' => $dataIT['id'] ?? $dataEN['id'] ?? null,
		        'original_language' => $dataIT['original_language'] ?? $dataEN['original_language'] ?? 'Non disponibile',
		        'png' => $dataIT['poster_path'] ?? $dataEN['poster_path'] ?? 'Non disponibile',
		        'runtime' => $dataIT['runtime'] ?? $dataEN['runtime'] ?? 'Non disponibile',
		        'overview' => $dataIT['overview'] ?? $dataEN['overview'] ?? 'Non disponibile',
		        'release' => $dataIT['release_date'] ?? $dataEN['release_date'] ?? 'Non disponibile',
		        'status' => $dataIT['status'] ?? $dataEN['status'] ?? 'Non disponibile',
		        'budget' => isset($dataIT['budget']) && $dataIT['budget'] != 0
		            ? $dataIT['budget']
		            : ($dataEN['budget'] ?? 'Non disponibile'),
		        'revenue' => isset($dataIT['revenue']) && $dataIT['revenue'] != 0
		            ? $dataIT['revenue']
		            : ($dataEN['revenue'] ?? 'Non disponibile'),
		        'tagline' => $dataIT['tagline'] ?? $dataEN['tagline'] ?? 'Non disponibile',
		        'vote_average' => $dataIT['vote_average'] ?? $dataEN['vote_average'] ?? 'Non disponibile',
		    ];
		}

		function getImages($movieID, $apiKey)
		{
		    $urlIT = "https://api.themoviedb.org/3/movie/{$movieID}/images?api_key={$apiKey}&language=it";
		    $urlEN = "https://api.themoviedb.org/3/movie/{$movieID}/images?api_key={$apiKey}&language=en";

		    $dataIT = json_decode(@file_get_contents($urlIT), true) ?: [];
		    $dataEN = json_decode(@file_get_contents($urlEN), true) ?: [];

		    $posters = $dataIT['posters'] ?? [];
		    $backdrops = $dataEN['backdrops'] ?? [];

		    $posterUrls = [];
		    foreach ($posters as $poster) {
		        if (isset($poster['file_path'])) {
		            $posterUrls[] = "https://image.tmdb.org/t/p/original" . $poster['file_path'];
		        }
		    }

		    $backdropUrls = [];
		    foreach ($backdrops as $backdrop) {
		        if (isset($backdrop['file_path'])) {
		            $backdropUrls[] = "https://image.tmdb.org/t/p/original" . $backdrop['file_path'];
		        }
		    }

		    $merged = array_merge($backdropUrls, $posterUrls);

		    if (!empty($merged)) {
		        array_unshift($merged, count($backdropUrls));
		        return $merged;
		    }

		    // Fallback in caso di errore o dati assenti
		    return [0];
		}

		function salvaFilmPreferito($connection, $IDfilm, $titolo, $percorso, $fav, $username) {
		    if ($username === "Ospite") {
		        return "Devi essere un utente registrato per salvare un film";
		    }

		    if ($fav === 'false') {
		        // Inserisci film (usa INSERT IGNORE per evitare duplicati)
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
		            ':username' => $username
		        ]);
		    } else {
		        // Rimuovi preferenza
		        $deletePref = "DELETE FROM preferisce WHERE IDfilm = :IDfilm AND username = :username";
		        $stmtDel = $connection->prepare($deletePref);
		        $stmtDel->execute([
		            ':IDfilm' => $IDfilm,
		            ':username' => $username
		        ]);
		    }
		    return null; // Nessun errore
		}

		$id = $_GET['id'];
		$data = getData("$id", $apiKey);
		$names = getGenres("$id", "$apiKey", $apiKey);
		$productor = getProduction("$id", $apiKey);
		$links = getVideos("$id", $apiKey);
		$cast = getCast("$id", $apiKey);
		$director = getCrewByJob("$id", $apiKey, "Director");
		$writer = getCrewByJob("$id", $apiKey, "Story");

		$writersAdditional = getCrewByJob($id, $apiKey, "Writer");
		// Se entrambi non contengono solo "Non disponibile", li unisci
		if ($writer === ["Non disponibile"]) {
		    $writer = $writersAdditional;
		} elseif ($writersAdditional !== ["Non disponibile"]) {
		    // Unisci senza duplicati
		    $writer = array_unique(array_merge($writer, $writersAdditional));
		}

		$screenplay = getCrewByJob("$id", $apiKey, "Screenplay");
		$producer = getCrewByJob("$id", $apiKey, "Producer");
		$images = getImages("$id", $apiKey);

		echo "<div class='padding'>";

		// Format dates
		$year = new DateTime($data['release']);
		$y = $year->format('Y');
		$date1 = $year->format('d-m-Y');
		$budget = is_int($data['budget']) ? number_format($data['budget'], 0) . "$" : $data['budget'];
		$revenue = is_int($data['revenue']) ? number_format($data['revenue'], 0) . "$" : $data['revenue'];

		echo "<div class='grey'> 
		        <img id='scheda' src='https://image.tmdb.org/t/p/w500{$data['png']}'>
			      <div id='info' class='overflow'>
			        <h1>{$data['title']}</h1> <br/>
			        <h2> {$y} / {$data['runtime']}m / " . implode(", ", $names) . "

			        <br/><br/><br/><br/>

			        <b>Titolo originale: </b>{$data['original_title']}<br/>

			        <b> Regia: </b>" . implode(", ", $director) . "<br/>

			        <br> <b> Soggetto: </b>" . implode(", ", $writer) . "
			        <br> <b> Sceneggiatura: </b>" . implode(", ", $screenplay) . "<br/>

			        <b> Produzione: </b>" . implode(", ", $producer) . "<br/><br/>

			        <b> Trama: </b>{$data['overview']}<br/><br/>

			        <b> Data di uscita: </b>{$date1}<br/>
			        <b> Budget: </b> {$budget} <br/>
			        <b> Incasso: </b> {$revenue} <br/>
			        <b> Produzione: </b>" . implode(", ", $productor) . "<br/><br/><br/></h2>
			      </div>
			    </div>";
	
		function isFav($id, $connection)
		{		
			$user = $_SESSION['user'];		
			$query = $connection->query("SELECT titolo FROM film, preferisce, utente
				WHERE utente.username = preferisce.username
				AND preferisce.IDfilm = film.IDfilm
				AND preferisce.IDfilm = '$id'
				AND preferisce.username = '$user'");
			$row = $query->fetch();

			return $row ? true : false;
		}	

		$fav = isFav($data['id'], $connection) ? 'true' : 'false';
		
		echo "
		<form action='save.php' method='GET'>
		    <input class='hid' type='hidden' name='IDfilm' value='".htmlspecialchars($data['id'], ENT_QUOTES)."'>
		    <input class='hid' type='hidden' name='titolo' value='".htmlspecialchars($data['title'], ENT_QUOTES)."'>
		    <input class='hid' type='hidden' name='percorso' value='".htmlspecialchars($data['png'], ENT_QUOTES)."'>
		    <input class='hid' type='hidden' name='fav' value='$fav'>	
		    <input type='submit' value='' id='salvaElemento'>
		</form>
		<script>
		    const fav = '$fav' === 'true'; // boolean true/false
		    const btn = document.getElementById('salvaElemento');
		    if (fav) {
		        btn.style.backgroundImage = 'url(media/fav1.png)';
		    } else {
		        btn.style.backgroundImage = 'url(media/fav0.png)';
		    }
		</script>";
	?>

	<script type="text/javascript">
	    <?php
	        $js_array = json_encode($links);
	        $length = count($links);
	        echo "var links = $js_array;\n";
	        echo "var length = $length;\n";
	    ?>
	    let i = 0;

	    function change() {            
	        document.getElementById("frame").src = "https://www.youtube.com/embed/" + links[i] + "?rel=0"; 
	        document.getElementById("index").innerHTML = (i + 1) + "/" + length;
	        size();
	        mobile();
	        simili();
	        <?php if (!empty($images) && $images[0] != 0) echo "images();"; ?>
	    }
	    
	    function size() {
	        const frame = document.getElementById("frame");
	        const width = frame.clientWidth;
	        const height = width * 9 / 16;  

	        frame.height = Math.round(height);

	        const trailerDiv = document.getElementById('trailer');
	        const buttonY = trailerDiv.clientHeight / 2 - 125;

	        document.getElementById("next").style.bottom = buttonY + "px";
	        document.getElementById("previus").style.bottom = buttonY + "px";
	    }

	    function next() {
	        i = (i + 1) % length;
	        change();
	        document.getElementById("index").innerHTML = (i + 1) + "/" + length;
	    }

	    function previus() {
	        i = (i - 1 + length) % length;
	        change();
	        document.getElementById("index").innerHTML = (i + 1) + "/" + length;
	    }
	</script>

	<div id="trailer" style="margin-top: 30px;"><br/>    
	    <h2>Guarda ora il trailer:<br/><br/></h2>
	    <div id="video">
	        <span id="previus" onclick="previus()">
	            <img src="media/arrow1.png" alt="Freccia precedente">
	        </span>
	        <iframe id="frame" src="" allowfullscreen width="70%" height="400"></iframe>
	        <span id="next" onclick="next()">
	            <img src="media/arrow1.png" alt="Freccia successiva" style="transform:rotate(180deg);">
	        </span>
	        <br/>
	    </div>
	    <span id="index" style="font-size: 22px;"></span>
	    <br/>
	</div>

	<?php
		echo "<div id='attori' class='grey'>
		    <center>
			    <h2>Ecco chi ha recitato in questo film:</h2>
			    <br/><br/>
			    <div id='scia'>";
			for ($i = 0; $i < count($cast); $i += 3) {
			    echo "<div id='cast'>
			            <img class='cast' src='" . htmlspecialchars($cast[$i + 2], ENT_QUOTES) . "' alt='Foto attore'>
			            <br/>
			            <p><b>" . htmlspecialchars($cast[$i + 1], ENT_QUOTES) . "</b></p>
			            <p class='character'>" . htmlspecialchars($cast[$i], ENT_QUOTES) . "</p>
			          </div>";
			}
			echo "</div>
			    <button onclick='start()' class='hidebutton'>
			        <img id='start' src='media/arrow1.png' alt='Start'>
			    </button>
			    <button onclick='end()' class='hidebutton'>
			        <img id='end' src='media/arrow1.png' alt='End' style='transform:rotate(180deg);'>
			    </button>
			</center>
		</div>";
	?>

	<script>
		function mobile() {
			const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
			if (isMobile) {
				const startBtn = document.getElementById("start");
				const endBtn = document.getElementById("end");
				if (startBtn) startBtn.style.display = "none";
				if (endBtn) endBtn.style.display = "none";
			}			
		}

		function end() {
			const scia = document.getElementById("scia");
			if (scia) scia.scrollBy(600, 0);
		}

		function start() {
			const scia = document.getElementById("scia");
			if (scia) scia.scrollBy(-600, 0);
		}
	</script>

	<center>
		<?php
			echo "<h2> Ecco le immagini promozionali: </h2><br>";

			if ($images[0] == 0) 
			    echo "Purtroppo non è disponibile alcuna immagine <br><br>";
			else {
			    echo "
			    <img id='presentazione' src='" . htmlspecialchars($images[1], ENT_QUOTES) . "'>
			    <div id='images'>";

			    $n = count($images);
			    $backdrop = $images[0];
			    for ($i = 1; $i < $n; $i++) {
			        $class = ($i < $backdrop) ? "minibackdrop" : "miniposter";
			        echo "<div id='image'>
			                <span class='span' onclick='cambia(this.id);' id='4'>
			                    <img class='created $class' src='" . htmlspecialchars($images[$i], ENT_QUOTES) . "' style=\"cursor: pointer;\">
			                </span><br>
			              </div>";
			    }
			    echo "</div>";
			}
		?>

		<script>
			<?php if($images[0] != 0): ?>
				var n = <?= count($images) - 1 ?>;
			<?php endif; ?>

			function cambia(click) {					
				var image = document.getElementById('presentazione');
				var mini = document.getElementsByClassName('created');						
				image.src = mini[click].src;				

				images();
			}

			function images() {				
				<?php if($images[0] != 0): ?>
					var image = document.getElementById('presentazione');
					var x = image.clientWidth;
					var y = image.clientHeight;
					if (x > y) {
						image.classList.add('backdrop'); 
						image.classList.remove('poster');
					} else {
						image.classList.add('poster'); 
						image.classList.remove('backdrop');
					}

					inizio();
				<?php endif; ?>
			}

			function inizio() {				
				var images = document.getElementsByClassName('created');
				var spans = document.getElementsByClassName('span');
				for(var i = 0; i < n; i++) {
					images[i].id = i;
					spans[i].id = i;
				}
			}
		</script>
		<br/><br/>

		<div class="grey">
			<script>
				const scorrimenti = {
					1366: { margin: 18, scroll: 1209 },
					1920: { margin: 8, scroll: 1752 }
				};

				let scorrimento = 1700;

				function simili() {
					const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
					const freccia0 = document.querySelector('.freccia0');
					const freccia1 = document.querySelector('.freccia1');
					const trail = document.querySelector('.trail');
					const h3 = document.querySelector('h3');

					if (isMobile) {
						if (freccia0) freccia0.style.display = 'none';
						if (freccia1) freccia1.style.display = 'none';
						if (trail) trail.style.width = '100%';
						if (h3) h3.style.margin = '0';
					} else {
						const x = screen.width;
						let margin = 0;

						if (scorrimenti[x]) {
							margin = scorrimenti[x].margin;
							scorrimento = scorrimenti[x].scroll;
						}

						const xx = x - 140 - 16;
						const larghezza = (xx * 100) / x;

						if (trail) trail.style.width = larghezza + '%';
						if (h3) h3.style.width = larghezza + '%';

						const movies = document.getElementsByClassName('movie');
						for (let i = 0; i < 20 && i < movies.length; i++) {
							movies[i].style.marginRight = margin + 'px';
						}
					}
				}

				function prossimo(id) {
					const el = document.getElementById(id);
					if (el) el.scrollBy(scorrimento, 0);
				}

				function precedente(id) {
					const el = document.getElementById(id);
					if (el) el.scrollBy(-scorrimento, 0);
				}
			</script>
		</center>	

		<?php
			function getSimilarMovies($path)
			{
				$json = file_get_contents($path);
				$result = json_decode($json, TRUE);		
				$movie;

				for ($i = 0, $k =0; $i < 20; $i++, $k=$k+3)
				{
					$movie[$k] = $result['results'][$i]['id'];
					$movie[$k+1] = $result['results'][$i]['poster_path'];
					$movie[$k+2] = $result['results'][$i]['title'];
				}

				return $movie;
			}

			$simili = getSimilarMovies("https://api.themoviedb.org/3/movie/".$id."/similar?api_key=".$apiKey."&language=it-IT&page=1");
		?>

		<div class='grey'>
			<br/><br/>
			<h3> Altri film che potrebbero interessarti: </h3>
			<div class='scorri'>
				<div class='freccia0' onclick="precedente('simili')"> 
					<img src="media/arrow2.png" width=60px>
				</div>
				<div class='trail' id='simili'>
					<?php	
						for($i=0; $i < count($simili); $i=$i+3)								
							echo "
							<div class='movie'>
								<a href='schedaFilm.php?id=".$simili[$i]."'> 
									<img class='home' src='https://image.tmdb.org/t/p/w500".$simili[$i+1]."'>
								</a><br/><br/> 
							</div>";						
					?>	
				</div> 
				<div class='freccia1' onclick="prossimo('simili')"> 
					<img src="media/arrow2.png" width=60px style="transform:rotate(180deg)">
				</div>
			</div>
			<br/><br/>
			<div style="position:relative; bottom: 10px;"/>	
		</div>
	</body>
</html>