<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $userName; global $allowNsfw;
if (!isset($_GET['id'])) die();

if (isset($_GET["nsfw"]) && !$allowNsfw) {
    unset($_GET["nsfw"]);
}

$follows = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/follows.json"), true);

if (isset($_GET['follow'])) {
    if (in_array($_GET['id'], $follows[$userName])) {
        unset($follows[$userName][array_search($_GET['id'], $follows[$userName])]);
        $follows[$userName] = array_values(array_filter($follows[$userName], function ($i) { return isset($i); }));
    } else {
        $follows[$userName][] = $_GET['id'];
    }

    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/follows.json", json_encode($follows, JSON_PRETTY_PRINT));

    header("Location: /tag?id=" . $_GET['id']);
    die();
}

$tags = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tags.json"), true);
$name = isset($tags["db"][$_GET["id"]]) && isset($tags["db"][$_GET["id"]]["display_name"]) ? $tags["db"][$_GET["id"]]["display_name"] : ucwords(strip_tags($_GET["id"]));

$title = $name; require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php";
$filters = parseFilters(json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/filters.json"), true));

$page = 1;

if (isset($_GET["page"]) && is_numeric($_GET["page"]) && (int)$_GET["page"] > 0) {
    $page = $_GET["page"];
}

?>

<div style="margin: 20px 50px 0;" class="main-app">
    <h2><?= $title ?></h2>
    <p><a href="/tag?id=<?= $_GET['id'] ?>&follow"><?= in_array($_GET['id'], $follows[$userName]) ? 'Unfollow tag' : 'Follow tag' ?></a><?php if ($allowNsfw): ?> ·
    <?php if (isset($_GET['nsfw']) && !isset($_GET['only_nsfw'])): ?>
    <a href="/tag?id=<?= $_GET['id'] ?>&page=<?= $page ?>&nsfw&only_nsfw">Show only NSFW</a>
    <?php elseif (isset($_GET['nsfw']) && isset($_GET['only_nsfw'])): ?>
    <a href="/tag?id=<?= $_GET['id'] ?>&page=<?= $page ?>">Hide NSFW</a>
    <?php else: ?>
    <a href="/tag?id=<?= $_GET['id'] ?>&page=<?= $page ?>&nsfw">Show NSFW</a>
    <?php endif; ?><?php endif; ?>
    </p>

    <div style="display: grid; grid-template-columns: repeat(6, 1fr); grid-gap: 10px;" id="grid">Loading...</div>
    <p style="text-align: center; display: none;" id="pagination"><a href="/tag?id=<?= $_GET['id'] ?>&page=<?= max($page - 1, 1) ?><?= isset($_GET['nsfw']) ? '&nsfw' : '' ?><?= isset($_GET['only_nsfw']) ? '&only_nsfw' : '' ?>"><</a> <b>Page <?= $page ?></b> <a href="/tag?id=<?= $_GET['id'] ?>&page=<?= $page + 1 ?><?= isset($_GET['nsfw']) ? '&nsfw' : '' ?><?= isset($_GET['only_nsfw']) ? '&only_nsfw' : '' ?>">></a></p>

    <script>
        _display_filter = `(<?= $filters[isset($_GET['nsfw']) ? (isset($_GET['only_nsfw']) ? 'nsfw' : 'minimal') : 'default'] ?>), (<?= $_GET['id'] ?>)`;
        _display_page = <?= $page ?>;
    </script>
    <script src="/assets/display.js"></script>
</div>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
