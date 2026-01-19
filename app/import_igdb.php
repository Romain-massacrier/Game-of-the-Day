<?php
require_once __DIR__ . "/init_db.php";
require_once __DIR__ . "/igdb.php";

init_db();
$pdo = db();

$wanted = [
    "Super Mario Bros.",
    "The Legend of Zelda: A Link to the Past",
    "Sonic the Hedgehog 2",
    "Final Fantasy VII",
    "GoldenEye 007",
];

$pdo->beginTransaction();

try {
    // Option: on repart propre
    $pdo->exec("DELETE FROM game_images;");
    $pdo->exec("DELETE FROM games;");

    $insertGame = $pdo->prepare("
        INSERT INTO games (title, platform, release_date, genre, summary, developer, publisher)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $insertImage = $pdo->prepare("
        INSERT INTO game_images (game_id, type, url)
        VALUES (?, ?, ?)
    ");

    foreach ($wanted as $title) {
        // Requete IGDB: infos + cover + screenshots
        $q = "
            search \"" . addslashes($title) . "\";
            fields name, first_release_date, summary, cover.url, screenshots.url;
            limit 1;
        ";

        $res = igdb_request("games", $q);

        if (empty($res)) {
            echo "Introuvable: " . $title . PHP_EOL;
            continue;
        }

        $g = $res[0];

        $name = $g["name"] ?? $title;
        $summary = $g["summary"] ?? null;

        $release = null;
        if (!empty($g["first_release_date"])) {
            // epoch -> YYYY-MM-DD
            $release = gmdate("Y-m-d", (int)$g["first_release_date"]);
        }

        // Cover grande taille
        $cover = null;
        if (!empty($g["cover"]["url"])) {
            $cover = igdb_img($g["cover"]["url"], "t_cover_big");
        }

        // Screenshots grande taille
        $screens = [];
        if (!empty($g["screenshots"]) && is_array($g["screenshots"])) {
            foreach ($g["screenshots"] as $s) {
                if (!empty($s["url"])) {
                    $screens[] = igdb_img($s["url"], "t_screenshot_big");
                }
            }
        }

        // Pour une V1 import, on met une plateforme simple
        // On ameliorera ensuite avec le vrai mapping plateformes IGDB
        $platform = "IGDB";
        $genre = null;
        $developer = null;
        $publisher = null;

        $insertGame->execute([
            $name,
            $platform,
            $release,
            $genre,
            $summary,
            $developer,
            $publisher,
        ]);

        $gameId = (int)$pdo->lastInsertId();

        if ($cover) {
            $insertImage->execute([$gameId, "cover", $cover]);
        }

        // Limite a 6 screenshots pour rester leger
        $screens = array_slice($screens, 0, 6);
        foreach ($screens as $u) {
            $insertImage->execute([$gameId, "screenshot", $u]);
        }

        echo "Import OK: " . $name . PHP_EOL;

        // Petite pause pour eviter de spammer l API
        usleep(300000);
    }

    $pdo->commit();
    echo "Import termine" . PHP_EOL;

} catch (Throwable $e) {
    $pdo->rollBack();
    echo "Import ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
