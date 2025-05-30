<?php session_start();?>

<html>
	<head>
		<title>Il Mondo del Cinema </title>
		<link rel="stylesheet" href="style.css">
		<link rel="stylesheet" href="font.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	</head>
	
	<?php
		$_SESSION["user"] = "Ospite";
		$utente = htmlspecialchars($_SESSION["user"], ENT_QUOTES, 'UTF-8');			
	?>

	<body>
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

		<div class='padding grey' style="height: calc(100vh - 46px);">
			<p style="margin-top: 160px;">
				<center>
					<h2> Logout effetuato con successo </h2> <br>
					Sarai reindetizzato automaticamente all'indice...

					<script>
						setTimeout(() => {window.location.href = 'index.php'; }, 1000);
					</script>
				</center>	
			</p>
		</div>
	</body>
</html>