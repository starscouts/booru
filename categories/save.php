<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $userName; global $id;
while (trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json")) === "") {}

$isSaved = false;

$saved = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json"), true);
$id = substr(bin2hex(random_bytes(32)), 0, 16);

if ($_GET["category"] === "new") $_GET["category"] = $id;
if (!isset($saved[$userName][$_GET["category"]])) $saved[$userName][$_GET["category"]] = [
    "name" => "New category " . $_GET["category"],
    "items" => []
];

if (!in_array($_GET["id"], $saved[$userName][$_GET["category"]]["items"])) {
    $saved[$userName][$_GET["category"]]["items"][] = $_GET["id"];
}

if (isset($saved[$userName][$_GET["category"]]["owner"]) && $saved[$userName][$_GET["category"]]["owner"] !== $id) {
    die();
}

file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json", json_encode($saved, JSON_PRETTY_PRINT));

while (trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json")) === "") {
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json", json_encode($saved, JSON_PRETTY_PRINT));
}

die("ok");