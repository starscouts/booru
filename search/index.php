<?php

$title = "Search"; require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php"; global $allowNsfw;
$filters = parseFilters(json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/filters.json"), true));

$page = 1;

if (isset($_GET['type']) && ($_GET['type'] === "nsfw" || $_GET['type'] === "all") && !$allowNsfw) {
    $_GET['type'] = "sfw";
}

if (isset($_GET["page"]) && is_numeric($_GET["page"]) && (int)$_GET["page"] > 0) {
    $page = $_GET["page"];
}

?>

<div style="margin: 20px 50px 0;" class="main-app">
    <h2>Search</h2>
    <form style="display: grid; grid-template-columns: <?php if (!$allowNsfw): ?>4fr 0.25fr<?php else: ?>1fr 3fr 0.25fr<?php endif; ?> ;grid-gap: 15px;" action="/search">
        <?php if ($allowNsfw): ?>
        <select name="type" class="form-select">
            <option value="sfw" <?= isset($_GET['type']) && $_GET['type'] === "sfw" ? "selected" : "" ?>>Normal images</option>
            <option value="nsfw" <?= isset($_GET['type']) && $_GET['type'] === "nsfw" ? "selected" : "" ?>>NSFW</option>
            <option value="all" <?= isset($_GET['type']) && $_GET['type'] === "all" ? "selected" : "" ?>>Everything</option>
        </select>
        <?php endif; ?>
        <input autocomplete="off" type="text" class="form-control" name="query" placeholder="Search query" value="<?= strip_tags($_GET['query'] ?? "") ?>">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if (isset($_GET['query'])):

    $type = "sfw";

    $query = strip_tags($_GET['query']);
    $filter = $filters["default"];

    if (isset($_GET["type"]) && $_GET['type'] === "nsfw") {
        $filter = $filters["nsfw"];
        $type = "nsfw";
    } else if (isset($_GET["type"]) && $_GET['type'] === "all") {
        $filter = $filters["minimal"];
        $type = "all";
    }

    if ($type !== "sfw") echo("<script>requestExplicit('back', false);</script>");

    ?>
    <hr>

    <div style="display: grid; grid-template-columns: repeat(6, 1fr); grid-gap: 10px;" id="grid">Loading...</div>
    <p style="text-align: center; display: none;" id="pagination"><a href="/search?type=<?= $type ?>&query=<?= $query ?>&page=<?= max($page - 1, 1) ?>"><</a> <b>Page <?= $page ?></b> <a href="/search?type=<?= $type ?>&query=<?= $query ?>&page=<?= $page + 1 ?>">></a></p>

    <script>
        _display_filter = `(<?= $filter ?>), (<?= $query ?>)`;
        _display_page = <?= $page ?>;
    </script>
    <script src="/assets/display.js"></script>
    <?php endif; ?>
</div>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
