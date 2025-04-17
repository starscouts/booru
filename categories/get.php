<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $userName; global $id;
while (trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json")) === "") {}

$uid = $id;

if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/nsfw.php")) require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/nsfw.php";

$list = [];

$saved = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json"), true)[$userName];
foreach ($saved as $id => $group) {
    if ($id === "favorites") continue;

    $item = [
        "id" => $id,
        "enabled" => !isset($group["owner"]) || $group["owner"] === $uid,
        "name" => $group["name"]
    ];

    $list[] = $item;
}

die(json_encode($list));