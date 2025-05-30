<?php
    $apiKey = "1333fbde4dbb13a1212b4c9484da5126";
    try {    
        //$connection = new PDO("mysql:host=sql111.infinityfree.com;port=3306;dbname=if0_39069812_moviedb", "if0_39069812", "JiH6qdE8dMaU");
        $connection = new PDO("mysql:host=localhost;dbname=tomasoni_user", "root", "");
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
    } catch (PDOException $e) {
        die("Connessione fallita: " . $e->getMessage());
    }
?>