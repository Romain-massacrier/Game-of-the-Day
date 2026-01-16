<?php

require_once __DIR__ . "/db.php";

function init_db(): void
{
    $pdo = db();
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS games(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    platform TEXT NOT NULL,
    release_date TEXT,
    genre TEXT,
    summary TEXT,
    developer TEXT,
    publisher TEXT
    )
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS game_images (
id INTEGER PRIMARY KEY AUTOINCREMENT,
game_id INTEGER NOT NULL,
type TEXT NOT NULL,
url TEXT NOT NULL
)
");
}