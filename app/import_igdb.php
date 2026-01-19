<?php
require_once __DIR__ . "/init_db.php";
require_once __DIR__ . "/igdb.php";

init_db();
$pdo = db();

// Liste simple: tu gères la plateforme toi meme (plus propre pour ton projet)
$wanted = [
    ["title" => "Super Mario Bros.", "platform" => "NES (8-bit)"],
    ["title" => "The Legend of Zelda: A Link to the Past", "platform" => "SNES (16-bit)"],
    ["title" => "Sonic the Hedgehog 2", "platform" => "Mega Drive (16-bit)"],
    ["title" => "Final Fantasy VII", "platform" => "PS1 (32-bit)"],
    ["title" => "GoldenEye 007", "platform" => "Nintendo 64 (64-bit)"],
];

$pdo->beginTransaction();

try {
    // On repart propre
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

    foreach ($wanted as $item) {
        $title = $item["title"];
        $platform = $item["platform"];

        // Requête IGDB: infos + cover + screenshots
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

        // epoch -> YYYY-MM-DD
        $release = null;
        if (!empty($g["first_release_date"])) {
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

        // Champs optionnels (on laissera null pour l instant)
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

        // Limite a 6 screenshots
        $screens = array_slice($screens, 0, 6);
        foreach ($screens as $u) {
            $insertImage->execute([$gameId, "screenshot", $u]);
        }

        echo "Import OK: " . $name . " (" . $platform . ")" . PHP_EOL;

        // petite pause pour eviter de spammer l API
        usleep(300000);
    }

    $pdo->commit();
    echo "Import termine" . PHP_EOL;

} catch (Throwable $e) {
    $pdo->rollBack();
    echo "Import ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
