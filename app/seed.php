<?php
require_once __DIR__ . "/init_db.php";

init_db();
$pdo = db();

$pdo->beginTransaction();

try {
    $pdo->exec("DELETE FROM game_images;");
    $pdo->exec("DELETE FROM games;");

    $games = [
    [
        "title" => "Super Mario Bros.",
        "platform" => "NES (8-bit)",
        "release_date" => "1985-09-13",
        "genre" => "Plateforme",
        "summary" => "Le classique qui a defini le platformer moderne.",
        "developer" => "Nintendo",
        "publisher" => "Nintendo",
        "images" => [
            ["type" => "cover", "url" => "https://via.placeholder.com/360x480?text=Super+Mario+Bros+Cover"],
            ["type" => "screenshot", "url" => "https://via.placeholder.com/800x450?text=Super+Mario+Bros+Screen+1"],
            ["type" => "screenshot", "url" => "https://via.placeholder.com/800x450?text=Super+Mario+Bros+Screen+2"],
        ],
    ],
    [
        "title" => "The Legend of Zelda: A Link to the Past",
        "platform" => "SNES (16-bit)",
        "release_date" => "1991-11-21",
        "genre" => "Aventure",
        "summary" => "Exploration, donjons, secrets. Une reference absolue.",
        "developer" => "Nintendo",
        "publisher" => "Nintendo",
        "images" => [
            ["type" => "cover", "url" => "https://via.placeholder.com/360x480?text=Zelda+ALTTP+Cover"],
            ["type" => "screenshot", "url" => "https://via.placeholder.com/800x450?text=Zelda+ALTTP+Screen+1"],
            ["type" => "screenshot", "url" => "https://via.placeholder.com/800x450?text=Zelda+ALTTP+Screen+2"],
        ],
    ],
    [
        "title" => "Sonic the Hedgehog 2",
        "platform" => "Mega Drive (16-bit)",
        "release_date" => "1992-11-24",
        "genre" => "Plateforme",
        "summary" => "Vitesse, level design, et une OST mythique.",
        "developer" => "Sega",
        "publisher" => "Sega",
        "images" => [
            ["type" => "cover", "url" => "https://via.placeholder.com/360x480?text=Sonic+2+Cover"],
            ["type" => "screenshot", "url" => "https://via.placeholder.com/800x450?text=Sonic+2+Screen+1"],
            ["type" => "screenshot", "url" => "https://via.placeholder.com/800x450?text=Sonic+2+Screen+2"],
        ],
    ],
    [
        "title" => "Final Fantasy VII",
        "platform" => "PS1 (32-bit)",
        "release_date" => "1997-01-31",
        "genre" => "JRPG",
        "summary" => "Un monument du JRPG.",
        "developer" => "Square",
        "publisher" => "Square",
        "images" => [
            ["type" => "cover", "url" => "https://via.placeholder.com/360x480?text=FF7+Cover"],
            ["type" => "screenshot", "url" => "https://via.placeholder.com/800x450?text=FF7+Screen+1"],
            ["type" => "screenshot", "url" => "https://via.placeholder.com/800x450?text=FF7+Screen+2"],
        ],
    ],
    [
        "title" => "GoldenEye 007",
        "platform" => "Nintendo 64 (64-bit)",
        "release_date" => "1997-08-25",
        "genre" => "FPS",
        "summary" => "Le FPS console culte, surtout en multi local.",
        "developer" => "Rare",
        "publisher" => "Nintendo",
        "images" => [
            ["type" => "cover", "url" => "https://via.placeholder.com/360x480?text=GoldenEye+007+Cover"],
            ["type" => "screenshot", "url" => "https://via.placeholder.com/800x450?text=GoldenEye+Screen+1"],
            ["type" => "screenshot", "url" => "https://via.placeholder.com/800x450?text=GoldenEye+Screen+2"],
        ],
    ],
];

    $insertImage = $pdo->prepare("
    INSERT INTO games (title, platform, release_date, genre, summary, developer, publisher)
    VALUES (?,?,?)
    ");

    $insertImage = $pdo->prepare("
    INSERT INTO game_images (game_id, type, url)
    VALUES (?,?,?)
    ");

    foreach ($games as $g) {
        $insertGame->execute([
            $g["title"]
            $g["platform"],
            $g["release_date"],
            $g["genre"],
            $g["summary"],
            $g["developer"],
            $g["publisher"],
        ]);

        $gameID = (int) $pdo->lastInsertId();

        foreach ($g["images"] as $img) {
            $insertImage->execute([$gameID, $img["type"], $img["url"]]);
        }
    }
    $pdo->commit();
    echo "Seed OK. Jeux inseres: " . count($games) . PHP_EOL;

} catch (Throwable $e) {
    $pdo->rollback();
    echo "Seed ERROR " . $e->getMessage() . PHP_EOL;
    exit(1);
}