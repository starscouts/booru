<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $userName;

if (!isset($_GET['tag'])) die();
$addTags = $_GET['tag'];

$tags = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tags.json"), true);

foreach (explode(",", $addTags) as $tag) {
    if (!in_array($tag, $tags["list"]) && !str_contains($tag, ":")) {
        $tags["list"][] = $tag;
    }
}

$db = $tags["db"];

foreach ($tags["list"] as $tag) {
    $info = [
        "reviewed" => isset($tags["db"][$tag]) && $tags["db"][$tag]["reviewed"] === "" ? "" : "// REPLACE THIS WITH AN EMPTY STRING ONCE REVIEWED //",
        "fetched" => isset($tags["db"][$tag]) ? !!$tags["db"][$tag]["fetched"] : false,
        "aliases" => isset($tags["db"][$tag]) ? $tags["db"][$tag]["aliases"] : [],
        "category" => isset($tags["db"][$tag]) ? $tags["db"][$tag]["category"] : null,
        "display_name" => isset($tags["db"][$tag]) ? $tags["db"][$tag]["display_name"] : null,
    ];

    $db[$tag] = $info;
}

$tags["db"] = $db;

file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tags.json", json_encode($tags, JSON_PRETTY_PRINT));
die("ok");