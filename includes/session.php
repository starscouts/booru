<?php

function formatPonypush($message) {
    return "Update to Ponypush 3.1.0 or later — (\$PA1$\$" . base64_encode($message) . "\$\$)";
}

if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/nsfw.php")) require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/nsfw.php";

$species = [
    "name" => "an earth pony",
    "code" => "earth",
    "tag" => "earth pony"
];

$name = null;
$nameChanged = false;

$names = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/names.json"), true);

if (!isset($_COOKIE["booru_auth"])) {
    header("Location: /auth");
    die();
} else {
    if (str_contains($_COOKIE['booru_auth'], ".") || str_contains($_COOKIE['booru_auth'], "/") || trim($_COOKIE["booru_auth"]) === "") {
        header("Location: /auth");
        die();
    }

    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tokens/" . str_replace(".", "", str_replace("/", "", $_COOKIE['booru_auth'])))) {
        header("Location: /auth");
        die();
    }
}

$debug = false;
if ($_SERVER['REQUEST_URI'] === "/debug/") {
    $debug = true;
    header("Content-Type: text/plain");
    echo("------------------------------\n");
    echo("NSFW FILTER DEBUG\n\n");
}

if (function_exists("proprietary_nsfw_1")) proprietary_nsfw_1();

global $userName;
$userName = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tokens/" . str_replace(".", "", str_replace("/", "", $_COOKIE['booru_auth']))), true)["login"];

global $allowNsfw;
$allowNsfw = false;

if (function_exists("proprietary_nsfw_2")) proprietary_nsfw_2();

$name = "-";

if ($debug) echo("Found PEH database:           ");

if (file_exists("/peh") && file_exists("/peh/gdapd") && file_exists("/peh/ynmuc")) {
    if ($debug) echo("yes\n");
    $fronters = json_decode(file_get_contents("/peh/" . ($userName === "raindrops" ? "gdapd" : "ynmuc") . "/fronters.json"), true);

    if ($debug) {
        echo("Found fronters data:          " . (isset($fronters) ? "yes (" . ($userName === "raindrops" ? "gdapd" : "ynmuc") . ")" : "no") . "\n");
    }

    if ($debug) echo("At least 1 pony at front:     ");

    if (count($fronters["members"]) > 0) {
        if ($debug) echo("yes (" . count($fronters["members"]) . ")\n");
        $name = $fronters["members"][0]["display_name"] ?? $fronters["members"][0]["name"];
        $id = $fronters["members"][0]["id"];
        $GLOBALS["ponyID"] = $id;

        $originalName = $name;

        if (isset($names[$id])) {
            $name = $names[$id];
            $nameChanged = true;
        }

        if ($debug) echo("Pony has metadata:            ");

        if (file_exists("/peh/metadata/" . $id . ".json")) {
            if ($debug) echo("yes (" . $id . ")\n");

            if (function_exists("proprietary_nsfw_3")) proprietary_nsfw_3($debug);

            $info = json_decode(file_get_contents("/peh/metadata/" . $id . ".json"), true);

            if (isset($info["species"][0])) {
                switch ($info["species"][0]) {
                    case "unicorn":
                        $species = [
                            "name" => "a unicorn",
                            "code" => "unicorn",
                            "tag" => "unicorn"
                        ];
                        break;

                    case "pegasus":
                        $species = [
                            "name" => "a pegasus",
                            "code" => "pegasus",
                            "tag" => "pegasus"
                        ];
                        break;

                    case "alicorn":
                        $species = [
                            "name" => "an alicorn",
                            "code" => "alicorn",
                            "tag" => "alicorn"
                        ];
                        break;

                    case "batpony":
                        $species = [
                            "name" => "a bat pony",
                            "code" => "batpony",
                            "tag" => "bat pony"
                        ];
                        break;
                }
            }

            if ($debug) echo("    Defined fixed age:        " . (isset($info["birth"]["age"]) && ($info["birth"]["age"] > 0 || $info["birth"]["age"] === -1) ? "yes (" . ($info["birth"]["age"] === -1 ? "eternal" : $info["birth"]["age"]) . ")" : "no") . "\n");
            if ($debug) echo("    Below 16 by fixed age:    ");

            if (isset($info["birth"]["age"]) && $info["birth"]["age"] < 15 && $info["birth"]["age"] > 0) {
                if ($debug) echo("yes <--\n");
                if ($debug) echo("    Has set birth year:       no\n");
                if ($debug) echo("    Calculated age over 15:   no\n");
                if ($debug) echo("    Is otherwise permitted:   no\n");
                $allowNsfw = false;
            } else if (isset($info["birth"]["year"]) && $info["birth"]["year"] > 1900) {
                if ($debug) echo("no\n");
                if (!isset($info["birth"]["date"])) $info["birth"]["date"] = "01-01";

                $age = (int)date('Y') - $info["birth"]["year"] + (strtotime(date('Y') . "-" . $info["birth"]["date"]) <= time() ? 0 : -1);

                if ($debug) echo("    Has set birth year:       yes (" . $info["birth"]["year"] . ", " . $age . ")\n");

                if ($age < 15) {
                    if ($debug) echo("    Calculated age over 15:   no <--\n");
                    $allowNsfw = false;
                } else {
                    if ($debug) echo("    Calculated age over 15:   yes <--\n");
                    $allowNsfw = true;
                }

                if ($debug) echo("    Is otherwise permitted:   no\n");
            } else if ((!isset($info["birth"]["age"]) || $info["birth"]["age"] === 0) && (!isset($info["birth"]["year"]) || $info["birth"]["year"] > 1900)) {
                if ($debug) echo("no\n");
                if ($debug) echo("    Has set birth year:       no\n");
                if ($debug) echo("    Calculated age over 15:   no\n");
                if ($debug) echo("    Is otherwise permitted:   no <--\n");
                $allowNsfw = false;
            } else {
                if ($debug) echo("no\n");
                if ($debug) echo("    Has set birth year:       no\n");
                if ($debug) echo("    Calculated age over 15:   no\n");
                if ($debug) echo("    Is otherwise permitted:   yes <--\n");
                $allowNsfw = true;
            }

            if (function_exists("proprietary_nsfw_4")) proprietary_nsfw_4($debug, $id, $info);
        } else {
            if ($debug) echo("no, stopping here\n");
        }
    } else {
        if ($debug) echo("no, stopping here\n");
    }
} else {
    if ($debug) echo("no, stopping here\n");
}

if (function_exists("proprietary_nsfw_5")) {
    $allowNsfwGeneral = $allowNsfw || proprietary_nsfw_5();
} else {
    $allowNsfwGeneral = $allowNsfw;
}

if (str_starts_with($_SERVER['REQUEST_URI'], "/nsfw") && !$allowNsfwGeneral) {
    header("Location: /") and die();
}

if ($debug) {
    if (function_exists("proprietary_nsfw_6")) {
        proprietary_nsfw_6($name);
    } else {
        echo("\nAllowing NSFW content:        " . ($allowNsfw ? "yes" : "no"));
        echo("\nReport generated for:         " . $name . "\n");
    }

    echo("------------------------------\n");
}