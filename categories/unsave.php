<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $userName;
while (trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json")) === "") {}

$saved = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json"), true);
$removed = false;

foreach ($saved[$userName] as $name => $group) {
    foreach ($group["items"] as $index => $item) {
        if ($item === $_GET["id"]) {
            unset($saved[$userName][$name]["items"][$index]);
            $removed = true;
        }
    }
}

foreach ($saved[$userName] as $name => $group) {
    if (count($group["items"]) === 0) {
        unset($saved[$userName][$name]);
    }
}

file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json", json_encode($saved, JSON_PRETTY_PRINT));

while (trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json")) === "") {
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json", json_encode($saved, JSON_PRETTY_PRINT));
}

die($removed ? "ok" : "no");