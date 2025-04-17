<?php $title = "Explicit"; require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php"; global $allowNsfw;

?>

<div style="margin: 20px 50px 0;" class="main-app">
    <h2>Explicit</h2>
    <p>Select how you want to view explicit images:</p>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); grid-gap: 20px;" id="explicit-grid">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    <img class="icon" src="/assets/gallery.png" style="vertical-align: middle; width: 36px; height: 36px; margin-right: 2px;">
                    <span style="vertical-align: middle;">Regular gallery</span>
                </h4>
                <p>View images in a gallery-like mode, similar to regular Booru. Useful when you are looking for something in particular or finding new ideas to reproduce.</p>
                <a href="/nsfw/g" class="btn btn-primary <?= !$allowNsfw ? "disabled" : "" ?>">Open</a><br>
                <small class="text-muted">Built-in</small>
            </div>
        </div>

        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/card1.php")): require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/card1.php"; else: ?>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        <img class="icon" src="/assets/extension.svg" style="vertical-align: middle; width: 36px; height: 36px; margin-right: 2px;">
                        <span style="vertical-align: middle;" class="text-danger">Missing proprietary extension</span>
                    </h4>
                    <p>An additional option is available, but it requires a proprietary extension that is not currently installed. Install the extension and try again.</p>
                    <a href="#" class="btn btn-primary disabled <?= !$allowNsfw ? "disabled" : "" ?>">Open</a><br>
                    <small class="text-muted">Not installed</small>
                </div>
            </div>
        <?php endif; ?>

        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/card2-v2.php")): require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/card2-v2.php"; else: ?>
            <div class="card" style="opacity: .75;">
                <div class="card-body">
                    <h4 class="card-title">
                        <img class="icon" src="/assets/extension.svg" style="vertical-align: middle; width: 36px; height: 36px; margin-right: 2px;">
                        <span style="vertical-align: middle;" class="text-danger">Missing proprietary extension</span>
                    </h4>
                    <p>An additional option is available, but it requires a proprietary extension that is not currently installed. Install the extension and try again.</p>
                    <a href="#" class="btn btn-primary disabled <?= !$allowNsfw ? "disabled" : "" ?>">Open</a><br>
                    <small class="text-muted">Not installed</small>
                </div>
            </div>
        <?php endif; ?>

        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/card2.php")): require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/card2.php"; else: ?>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        <img class="icon" src="/assets/extension.svg" style="vertical-align: middle; width: 36px; height: 36px; margin-right: 2px;">
                        <span style="vertical-align: middle;" class="text-danger">Missing proprietary extension</span>
                    </h4>
                    <p>An additional option is available, but it requires a proprietary extension that is not currently installed. Install the extension and try again.</p>
                    <a href="#" class="btn btn-primary disabled <?= !$allowNsfw ? "disabled" : "" ?>">Open</a><br>
                    <small class="text-muted">Not installed</small>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
