<?php

function db(): PDO
{
    $dbPath = __DIR__ . "/../storage/games.sqlite";

    // s'assurer que le dossier storage existe
    if (!file_exists(dirname($dbPath))) {
        mkdir(dirname($dbPath), 0777, true);
    }
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}

// Crée une base games.sqlite si elle n’existe pas
// Se connecte à SQLite
// Retourne un objet PDO utilisable partout