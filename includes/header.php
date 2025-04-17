<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $allowNsfwGeneral;

if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/nsfw.php")) require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/nsfw.php";

function parseFilters($filters, $ignore = []) {
    if (function_exists("proprietary_nsfw_filter")) $filters = proprietary_nsfw_filter($filters);

    foreach ($filters as $name => $filter) {
        $text = "";

        foreach ($filter as $item) {
            if (!in_array($item, $ignore)) {
                $text .= "-" . $item . ", ";
            }
        }

        $filters[$name] = substr($text, 0, -2);

        if ($name === "nsfw") {
            $text = "";

            foreach ($filter as $item) {
                if ($item === "close-up") continue;

                if (!in_array($item, $ignore)) {
                    $text .= "-" . $item . ", ";
                }
            }

            $filters["nsfw_closeup"] = substr($text, 0, -2);
        }
    }

    return $filters;
}

function removeFromNsfw($tag) {
    $filters = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/filters.json"), true);

    $text = "";
    $filter = $filters["nsfw"];

    foreach ($filter as $item) {
        if ($item !== $tag) {
            $text .= "-" . $item . ", ";
        }
    }

    return substr($text, 0, -2);
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <script src="/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <title><?= isset($title) ? $title . " - Booru" : "Booru" ?></title>
    <script>
        function sleep(ms) {
            return new Promise((res) => {
                setTimeout(res, ms);
            });
        }

        function fix3(number) {
            if (number < 10) {
                return number.toFixed(2);
            } else if (number < 100) {
                return number.toFixed(1);
            } else {
                return number.toFixed(0);
            }
        }

        function formatNumber(number) {
            if (number > 1000000000) {
                return fix3(number / 1000000000) + "B";
            } else if (number > 1000000) {
                return fix3(number / 1000000) + "M";
            } else if (number > 1000) {
                return fix3(number / 1000) + "K";
            } else {
                return number;
            }
        }
    </script>
    <style>
        blockquote {
            margin-left: 5px;
            padding-left: 10px;
            border-left: 5px solid rgba(255, 255, 255, .1);
        }

        .modal img {
            max-width: 100%;
        }

        @media (max-width: 900px) {
            #explicit-grid {
                grid-template-columns: 1fr 1fr !important;
            }
        }

        @media (max-width: 700px) {
            .main-app {
                margin-left: 20px !important;
                margin-right: 20px !important;
            }

            #grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }

            #grid .card {
                height: calc((100vw - 130px) / 3) !important;
            }

            form {
                grid-template-columns: 1fr !important;
                grid-gap: 5px !important;
            }

            .list-group-item.tag-item, .saved-list .list-group-item {
                grid-template-columns: 17vw 1fr !important;
            }

            #explicit-grid {
                grid-template-columns: 1fr !important;
            }

            form .btn {
                width: max-content;
                display: inline-block;
                margin-left: auto;
                margin-right: auto;
            }
        }

        :root {
            --bs-link-color: #7eb1fc;
            --bs-link-hover-color: #658dc9;
        }

        a {
            text-decoration: none;
        }

        .navbar-brand, .nav-link {
            color: white;
        }

        .navbar-brand:hover, .nav-link:hover {
            opacity: .75;
            color: white;
        }

        .navbar-brand:active, .nav-link:active, .navbar-brand:focus, .nav-link:focus {
            opacity: .5;
            color: white;
        }

        .form-control, .form-select, .form-check-input {
            filter: invert(1) hue-rotate(180deg) !important;
        }

        .icon {
            filter: invert(1) hue-rotate(180deg);
        }

        .progress {
            --bs-progress-bg: #252525;
        }

        .alert {
            filter: invert(1) hue-rotate(180deg);
        }

        .modal {
            backdrop-filter: blur(20px);
        }

        .modal-content {
            background-color: #222;
        }

        .dropdown-menu {
            filter: invert(1) hue-rotate(180deg);
        }

        .list-group-item {
            background-color: #222 !important;
            color: white !important;
        }

        .list-group-item.disabled {
            opacity: .75;
        }

        .list-group-item-action:hover {
            background-color: #272727;
            color: white;
        }

        .list-group-item-action:active, .list-group-item-action:focus {
            background-color: #323232;
            color: white;
        }

        .tag {
            filter: invert(1) hue-rotate(180deg) brightness(150%);
        }

        .modal-header {
            border-bottom-color: #333;
        }

        .navbar-toggler, .btn-close {
            filter: invert(1);
        }

        .navbar {
            color: white;
            background-color: #222 !important;
        }

        body, html {
            background-color: #111;
            color: white;
        }

        .card {
            background-color: #222;
        }
    </style>

    <?php if (str_contains($_SERVER['HTTP_USER_AGENT'], "+ColdHazeDesktop")): ?>
        <style>
            .navbar {
                display: none;
            }

            body, html {
                background-color: #222 !important;
            }

            .container {
                margin-left: 20px;
                margin-right: 20px;
                width: calc(100vw - 40px);
            }
        </style>
    <?php endif; ?>
</head>
<body>
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/explicit.php";

if (str_starts_with($_SERVER['REQUEST_URI'], "/nsfw/") && $_SERVER['REQUEST_URI'] !== "/nsfw/" && !str_starts_with($_SERVER["REQUEST_URI"], "/nsfw/?") && $_SERVER['REQUEST_URI'] !== "/nsfw/icon/" && $_SERVER['REQUEST_URI'] !== "/nsfw/icon") {
    echo("<script>requestExplicit('back', true);</script>");
}

?>
    <nav class="navbar navbar-expand-sm bg-light navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="/"><img src="/icon.svg" alt="Booru" style="width: 32px; height: 32px;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavbar">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/search"><img src="/assets/search.svg" style="width: 24px; margin-right: 5px;" class="icon"><span style="vertical-align: middle;">Search</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/followed"><img src="/assets/followed.svg" style="width: 24px; margin-right: 5px;" class="icon"><span style="vertical-align: middle;">Followed</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/saved"><img src="/assets/saved.svg" style="width: 24px; margin-right: 5px;" class="icon"><span style="vertical-align: middle;">Saved</span></a>
                    </li>
                    <?php if ($allowNsfwGeneral): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/nsfw/home"><img src="/assets/explicit.png" style="width: 24px; margin-right: 5px;" class="icon"><span style="vertical-align: middle;">Explicit</span></a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>