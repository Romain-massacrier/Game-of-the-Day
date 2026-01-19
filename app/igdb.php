<?php

function igdb_config(): array
{
    return require __DIR__ . "/config.php";
}

function igdb_get_token(): string
{
    $cfg = igdb_config();

     $url = "https://id.twitch.tv/oauth2/token"
        . "?client_id=" . urlencode($cfg["twitch_client_id"])
        . "&client_secret=" . urlencode($cfg["twitch_client_secret"])
        . "&grant_type=client_credentials";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 20,

        ]);

        $raw = curl_exec($ch);
    if ($raw === false) {
        throw new Exception("Curl token error: " . curl_error($ch));
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        throw new Exception("Token HTTP $status: $raw");
    }

    $data = json_decode($raw, true);
    if (!isset($data["access_token"])) {
        throw new Exception("Token JSON invalide");
    }

    return $data["access_token"];
}

function igdb_request(string $endpoint, string $query): array
{
    $cfg = igdb_config();
    $token = igdb_get_token();

    $ch = curl_init("https://api.igdb.com/v4/" . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $query,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            "Client-ID: " . $cfg["twitch_client_id"],
            "Authorization: Bearer " . $token,
            "Accept: application/json",
        ],
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        throw new Exception("Curl IGDB error: " . curl_error($ch));
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        throw new Exception("IGDB HTTP $status: $raw");
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new Exception("IGDB JSON invalide");
    }

    return $data;
}

function igdb_img(?string $url, string $size): ?string
{
    if (!$url) return null;

    if (str_starts_with($url, "//")) {
        $url = "https:" . $url;
    }

    return str_replace("/t_thumb/", "/" . $size . "/", $url);
}
