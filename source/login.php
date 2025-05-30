<?php 
session_start();
require_once 'db.php';

// Imposta utente come "Ospite" se non loggato
if (empty($_SESSION["user"]) || $_SESSION["user"] == 'Ospite')
    $_SESSION["user"] = "Ospite";			
$utente = htmlspecialchars($_SESSION["user"], ENT_QUOTES, 'UTF-8');

$username = $_GET["username"] ?? "";
$password = $_GET["password"] ?? "";
$login = isset($_GET["login"]) ? true : false;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Il Mondo del Cinema</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />   
</head>
<body id="index">
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
    	<center>
    		<form action="login.php" method="GET" autocomplete="off" style="margin-top: 160px;">
	            <label for="username"> Inserire il nome utente: </label><br/>
	            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required minlength="4" maxlength="20"/> <br/><br/>
	            
	            <label for="password">Inserire la password:</label><br/>
	            <input type="password" id="password" name="password" required minlength="4" /><br/><br/>
	            
	            <input type="submit" name="login" value="LOGIN" />       
	            <input type="submit" name="registrati" value="REGISTRATI" /><br/>       
        	</form>
    	</center>        

        <p>
            <?php
            if (!empty($username)) {
                echo "<center><br/>";
                $valido = true;

                if ($username === "Ospite") {
                    echo "Il nome utente non può essere 'Ospite'<br>";
                    $valido = false;
                } elseif (strlen($username) > 20) {
                    echo "Il nome inserito è troppo lungo<br>";
                    $valido = false;
                } elseif (strlen($username) < 4) {
                    echo "Il nome inserito è troppo corto<br>";
                    $valido = false;
                }

                if (strlen($password) < 4) {
                    echo "La password deve avere almeno 4 caratteri<br>";
                    $valido = false;
                }

                if ($valido) {
                    $connection = new PDO("mysql:host=localhost;dbname=tomasoni_user", "root", "");
                    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    if ($login) {
                        $stmt = $connection->prepare("SELECT username FROM utente WHERE username = ? AND password = ?");
                        $stmt->execute([$username, $password]);
                        $user = $stmt->fetch();

                        if (!$user) {
                            echo "Username o password incorretti";
                        } else {
                            $_SESSION["user"] = $username;
                            echo "<script>document.location.href = 'mieiDati.php';</script>";
                        }
                    } else {
                        $stmt = $connection->prepare("SELECT username FROM utente WHERE username = ?");
                        $stmt->execute([$username]);
                        $user = $stmt->fetch();

                        if ($user) {
                            echo "<br> Questo nome utente è già usato <br>";
                        } else {
                            $insert = "INSERT INTO utente VALUES ('$username', '$password', '".date("Y/m/d")."')";
							$query = $connection->prepare($insert);
							$query->execute();

                            $_SESSION['user'] = $username;
                            echo "Registrazione effettuata<br>";
                            echo "Sarai reindirizzato automaticamente all'indice...";
                            echo "<script>
                                    setTimeout(() => { window.location.href = 'index.php'; }, 1500);
                                  </script>";
                        }
                    }
                }
                echo "</center>";
            }
            ?>
        </p>
    </div>
</body>
</html>
