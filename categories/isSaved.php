<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $userName;
while (trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json")) === "") {}

$isSaved = false;

$saved = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json"), true)[$userName];
foreach ($saved as $group) {
    if (in_array($_GET["id"], $group["items"])) {
        $isSaved = true;
    }
}

die(json_encode([
    "value" => $isSaved
]));