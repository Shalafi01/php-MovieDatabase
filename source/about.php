<?php session_start();?>

<!DOCTYPE html>
<html lang="it">
	<head>
		<meta charset="UTF-8">
		<title>Il Mondo del Cinema</title>
		<link rel="stylesheet" href="style.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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

		<div class='padding' style="width: 60%; margin: auto; text-align: justify; ">		
			<p style="margin-top: 40px;">
				Ciao! Mi chiamo Nicola e sono uno sviluppatore indipendente. Questo sito è il mio progetto di maturità, nato dalla passione per il cinema e la tecnologia.<br><br>
			</p>
			<p style="margin-top: 100px;">
				<strong> CHANGELOG </strong> <br><br>

				(25/05/2025) Pulizia del codice e pubblicazione su GitHub <br><br>

				(18/07/2020) Ottimizzazione degli algoritmi, creata la pagina "About"<br><br>

				(25/05/2020) Sviluppata la versione 1.0 con tutte le funzionalità base.<br><br>
			</p>		
			<p style="margin-top: 150px;">
				<center>
					<strong> SOURCE </strong> <br><br>					
					<img src="media/TMDB.png">			
					<br><br>		
					This product uses the TMDb API but is not endorsed or certified by TMDb.
				</center>
			</p>	
		</div>
	</body>
</html>
