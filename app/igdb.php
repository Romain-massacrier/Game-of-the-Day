<?php

function igdb_config(): array
{
    return require __DIR__ . "/config.php";
}

function igdb_get_token(): string
{
    $cfg = igdb_config();

    $url = "https://id.twitch.tv/oauth2/token"
        . "?client_id" . urlencode($cfg["twich_client_id"])
        . "$client_secret=" . urlencode($cfg["twitch_client_"])
}