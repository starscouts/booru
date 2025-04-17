<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $userName;
while (trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json")) === "") {}

$isSaved = false;

$saved = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json"), true);

if ($_GET["id"] === "new" || $_GET["id"] === "category") die();
if (!isset($saved[$userName][$_GET["id"]])) die();

$saved[$userName][$_GET["id"]]["name"] = strip_tags($_GET["name"]);

file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json", json_encode($saved, JSON_PRETTY_PRINT));

while (trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json")) === "") {
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json", json_encode($saved, JSON_PRETTY_PRINT));
}

die("ok");