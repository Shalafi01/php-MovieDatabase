<?php
session_start();
require_once 'db.php';
?>

<html>
	<head>
		<title>Il Mondo del Cinema </title>
		<link rel="stylesheet" href="style.css">
		<link rel="stylesheet" href="font.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	</head>

	<body class="grey">	
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
			$n;
			$movie = salvati($connection);
			function salvati($connection)
			{
				$data;
				$i = 0;
				$user = $_SESSION['user'];		
				$query = $connection->query("SELECT film.IDfilm, film.percorso, film.titolo FROM film, preferisce, utente
						  					WHERE utente.username = preferisce.username
						  					AND preferisce.IDfilm = film.IDfilm
						  					AND preferisce.username = '$user'");

				$i=0;		
				while ($row = $query->fetch()) {
					$data[$i] = $row['IDfilm'];
					$data[$i+1] = $row['percorso'];
					$data[$i+2] = $row['titolo'];
					$i = $i+3;
				}

				return $data;
			}
			
			echo "<div class='grey padding'>			
			<br><br><br>
			<h3> Elementi Salvati: </h3> 
			<div class='risultati' style=\"margin-left: 70px;\">";
			if (!empty($movie))
			{
				for($i=0; $i < count($movie); $i=$i+3)
				{
					echo "<div class='movie' style=\"overflow-y: hidden;\">
					<a href='schedaFilm.php?id=".$movie[$i]."'> 
					<img class='home' src='https://image.tmdb.org/t/p/w500".$movie[$i+1]."'>
					</a> <br/> </div>";
				}
			}
			echo "</div>";
		?>		
	</body>
</html>


