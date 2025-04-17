<?php

$server = "auth.equestria.horse";

header("Content-Type: text/plain");

if (!isset($_GET['code'])) {
    die();
}

$appdata = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/oauth.json"), true);

$crl = curl_init('https://' . $server . '/hub/api/rest/oauth2/token');
curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($crl, CURLINFO_HEADER_OUT, true);
curl_setopt($crl, CURLOPT_POST, true);
curl_setopt($crl, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . base64_encode($appdata["id"] . ":" . $appdata["secret"]),
    "Content-Type: application/x-www-form-urlencoded",
    "Accept: application/json"
]);
curl_setopt($crl, CURLOPT_POSTFIELDS, "grant_type=authorization_code&redirect_uri=" . urlencode("https://booru.equestria.dev/auth/callback") . "&code=" . $_GET['code']);

$result = curl_exec($crl);
$result = json_decode($result, true);

curl_close($crl);

if (isset($result["access_token"])) {
    $crl = curl_init('https://' . $server . '/hub/api/rest/users/me');
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($crl, CURLINFO_HEADER_OUT, true);
    curl_setopt($crl, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $result["access_token"],
        "Accept: application/json"
    ]);

    $result = curl_exec($crl);
    $result = json_decode($result, true);

    if (!in_array($result["id"], $appdata["allowed"])) {
        header("Location: https://equestria.horse");
        die();
    }

    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tokens")) mkdir($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tokens");

    $token = bin2hex(random_bytes(32));
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tokens/" . $token, json_encode($result));
    header("Set-Cookie: booru_auth=" . $token . "; SameSite=None; Path=/; Secure; HttpOnly; Expires=" . date("r", time() + (86400 * 730)));

    header("Location: /");
    die();
}