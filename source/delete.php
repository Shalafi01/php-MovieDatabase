<?php
	session_start();
	require_once 'db.php';

	$username = $_SESSION["user"];

	try {
	    // Delete all user's favorite movies
	    $stmt = $connection->prepare("DELETE FROM preferisce WHERE username = :username");
	    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
	    $stmt->execute();

	    // Delete the user account
	    $stmt = $connection->prepare("DELETE FROM utente WHERE username = :username");
	    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
	    $stmt->execute();

	    // Reset session user to guest
	    $_SESSION["user"] = "Ospite";

	    // Redirect to homepage
	    header("Location: index.php");
	    exit;

	} catch (PDOException $e) {
	    // Handle any error during deletion
	    die("Error deleting user account: " . $e->getMessage());
	}
?>
