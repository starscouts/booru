<?php

$tags = json_decode(file_get_contents("./data/tags.json"), true);

foreach ($tags["db"] as $name => $tag) {
    if ($tag["fetched"]) continue;

    echo("$name\n");
    $data = json_decode(file_get_contents("https://derpibooru.org/api/v1/json/tags/" . urlencode($name), false, stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0 (+Booru/1.0; contact@minteck.org)\r\n"
        ]
    ])), true)["tag"];

    if (isset($data)) {
        echo("    Aliases: " . implode(", ", array_map(function ($i) { return urldecode($i); }, $data["aliases"] ?? [])) . "\n");
        echo("    Category: " . ($data["category"] ?? "(default)") . "\n");

        if (isset($data["category"]) && !in_array($data["category"], array_keys($tags["categories"]))) {
            $tags["categories"][$data["category"]] = "0,0,0";
        }

        $tag["aliases"] = array_unique([...$tag["aliases"], ...($data["aliases"] ?? [])]);
        $tag["category"] = $data["category"];
        $tag["fetched"] = true;
    } else {
        $tag["fetched"] = true;
    }
    
    $tag["reviewed"] = "// TAG IS AWAITING SECOND REVIEW //";

    $tags["db"][$name] = $tag;
    file_put_contents("./data/tags.json", json_encode($tags, JSON_PRETTY_PRINT));
}