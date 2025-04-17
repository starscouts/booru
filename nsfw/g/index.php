<?php $title = "Explicit Gallery"; require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $allowNsfw;
if (!$allowNsfw) header("Location: /nsfw/home") and die();

$page = 1;

if (isset($_GET["page"]) && is_numeric($_GET["page"]) && (int)$_GET["page"] > 0) {
    $page = $_GET["page"];
}

$filters = parseFilters(json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/filters.json"), true));

?>

<div style="margin: 20px 50px 0;" class="main-app">
    <div style="display: grid; grid-template-columns: repeat(6, 1fr); grid-gap: 10px;" id="grid">Loading...</div>

    <p style="text-align: center; display: none;" id="pagination"><a href="/nsfw/g?page=<?= max($page - 1, 1) ?>"><</a> <b>Page <?= $page ?></b> <a href="/nsfw/g?page=<?= $page + 1 ?>">></a></p>

    <script>
        _display_filter = `<?= $filters['nsfw'] ?>`;
        _display_page = <?= $page ?>;
    </script>
    <script src="/assets/display.js"></script>
</div>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
