<?php 
	session_start();
	require_once 'db.php';
?>

<html>
	<head>
		<title>Il Mondo del Cinema </title>
		<link rel="stylesheet" href="style.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	</head>
	<body>
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

		<div class='grey padding' style="height: calc(100vh - 46px);">

			<?php				
				$query = $connection->query("SELECT * FROM utente WHERE username ='".$_SESSION['user']."'");
				$row = $query->fetch();
				
				$data = date_create($row['dataR']);
				$data = date_format($data, 'd/m/Y');
				
				echo "
				<center>
					<p  style=\"margin-top: 80px;\">						 
						<h2> Bentornato ".$_SESSION['user']." </h2> 						
					</p>

				<br><br>
				<div id='mieiDati'>					
					Username: ".$row['username']." <br>
					Password: ".$row['password']."<br>
					Iscritto il giorno: $data<br><br><br>
				"; 		
			?>

			<button onclick='elimina()'> Elimina Account </button><br>
			<script>
			function elimina()
			{
				var r = confirm("Sicuro di voler eliminare il tuo account? L'operazione non può essere annullata");
				if (r == true)					
					window.location = "delete.php";					
			}
			</script>

			<?php
				echo "<br> <br> <br> </div>";
				echo "</center>";
			?>
		</div>
	</body>
</html>