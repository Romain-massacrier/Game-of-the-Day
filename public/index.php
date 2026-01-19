<?php
require_once __DIR__ . "/../app/init_db.php";

init_db();
$pdo = db();

/*
  1) On cherche un jeu sorti aujourd'hui (jour + mois)
  2) Sinon, on prend un jeu au hasard
*/
$sqlToday = "
    SELECT *
    FROM games
    WHERE release_date IS NOT NULL
      AND strftime('%m-%d', release_date) = strftime('%m-%d', 'now', 'localtime')
    ORDER BY release_date DESC
    LIMIT 1
";

$stmt = $pdo->query($sqlToday);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

$mode = "anniversaire";

if (!$game) {
    $mode = "random";
    $stmt = $pdo->query("SELECT * FROM games ORDER BY RANDOM() LIMIT 1");
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$game) {
    die("Aucun jeu en base. Lance le seed ou l import IGDB.");
}

// Images du jeu
$imgStmt = $pdo->prepare("
    SELECT *
    FROM game_images
    WHERE game_id = ?
    ORDER BY id ASC
");
$imgStmt->execute([(int)$game["id"]]);
$images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

function e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, "UTF-8");
}

$today = date("d/m/Y");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Game of the Day</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0f0f12;
            color: #f2f2f2;
        }
        .wrap {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 16px;
        }
        .top {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 12px;
        }
        .muted {
            opacity: 0.7;
        }
        .card {
            background: #1b1b22;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        img.cover {
            width: 240px;
            height: 320px;
            object-fit: cover;
            border-radius: 10px;
            background: #000;
        }
        .badge {
            display: inline-block;
            background: #2c2c38;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            margin-right: 8px;
            margin-bottom: 6px;
        }
        a {
            color: #9bdcff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="wrap">

    <div class="top">
        <h1 style="margin:0;">Game of the Day</h1>
        <div class="muted">Aujourd’hui: <?= e($today) ?></div>
    </div>

    <p class="muted">
        Mode: <?= $mode === "anniversaire" ? "sorti ce jour-là" : "random" ?>
    </p>

    <div class="card">
        <?php if (!empty($images)): ?>
            <img
                class="cover"
                src="<?= e($images[0]["url"]) ?>"
                alt="<?= e($game["title"]) ?>"
            >
        <?php endif; ?>

        <div>
            <h2 style="margin:0 0 8px;">
                <a href="game.php?id=<?= (int)$game["id"] ?>">
                    <?= e($game["title"]) ?>
                </a>
            </h2>

            <div style="margin-bottom:12px;">
                <span class="badge"><?= e($game["platform"]) ?></span>

                <?php if (!empty($game["release_date"])): ?>
                    <span class="badge"><?= e($game["release_date"]) ?></span>
                <?php endif; ?>

                <?php if (!empty($game["genre"])): ?>
                    <span class="badge"><?= e($game["genre"]) ?></span>
                <?php endif; ?>
            </div>

            <p style="line-height:1.5; margin:0;">
                <?= e($game["summary"] ?? "") ?>
            </p>

            <p style="margin-top:14px;">
                <a href="game.php?id=<?= (int)$game["id"] ?>">
                    Voir la fiche complète
                </a>
            </p>
        </div>
    </div>

</div>
</body>
</html>
