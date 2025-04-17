<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $userName; global $allowNsfw;
$saved = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/saved.json"), true)[$userName];

$title = "Saved";

if (isset($_GET["id"])) {
    $category = $saved[$_GET["id"]];

    if (!isset($category)) {
        header("Location: /saved");
        die();
    }

    if (isset($category["owner"]) && $category["owner"] !== $id) {
        header("Location: /saved");
        die();
    }

    $title = $category["name"] . " - Saved";
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php";
$uid = $GLOBALS["ponyID"];

?>

<?php if (!isset($_GET["id"])): ?>
<div style="margin: 20px 50px 0;" class="main-app">
    <div class="list-group saved-list">
        <?php foreach ($saved as $id => $category): if ($id === "favorites"): $category["items"] = array_filter(array_values($category["items"]), function ($i) { return isset($i); }) ?>
            <a href="/saved/?id=<?= $id ?>" class="list-group-item list-group-item-action" style="display: grid; grid-template-columns: 5vw 1fr; grid-gap: 15px;">
                <div style="background-color: rgba(255, 255, 255, .1); height: 64px; border-radius: 5px;">
                    <div style="opacity: 0; transition: opacity 200ms; background-size: cover; background-position: center; height: 100%; width: 100%; border-radius: 5px;" class="category-img" data-category-img="<?= count($category["items"]) > 0 ? $category["items"][count($category["items"]) - 1] : "" ?>">
                        <div style="display:none;width: 100%;backdrop-filter: blur(5px);height: 100%;border-radius: 5px;"></div>
                    </div>
                </div>
                <div style="display: flex; align-items: center;">
                    <div>
                        <b><?= $category["name"] ?></b><br>
                        <?= count($category["items"]) ?> image<?= count($category["items"]) > 1 ? "s" : "" ?>
                    </div>
                </div>
            </a>
        <?php endif; endforeach; ?>
    </div>

    <div class="list-group saved-list" style="margin-top: 10px;">
        <?php foreach ($saved as $id => $category): if ($id !== "favorites"): ?>
            <a href="/saved/?id=<?= $id ?>" class="list-group-item list-group-item-action <?= isset($category["owner"]) && $category["owner"] !== $uid ? "disabled" : "" ?>" style="display: grid; grid-template-columns: 5vw 1fr; grid-gap: 15px;">
                <div style="background-color: rgba(255, 255, 255, .1); height: 64px; border-radius: 5px;">
                    <div style="opacity: 0; transition: opacity 200ms; background-size: cover; background-position: center; height: 100%; width: 100%; border-radius: 5px;" class="category-img" data-category-img="<?= count($category["items"]) > 0 ? $category["items"][count($category["items"]) - 1] : "" ?>">
                        <div style="display:none;width: 100%;backdrop-filter: blur(<?= isset($category["owner"]) && $category["owner"] !== $uid ? "10px" : "5px" ?>);height: 100%;border-radius: 5px;"></div>
                    </div>
                </div>
                <div style="display: flex; align-items: center;">
                    <div>
                        <b><?= $category["name"] ?></b><br>
                        <?= count($category["items"]) ?> image<?= count($category["items"]) > 1 ? "s" : "" ?>
                    </div>
                </div>
            </a>
        <?php endif; endforeach; ?>
    </div>
</div>

<script>
    function showBackgroundImage(item, url, blur) {
        if (blur) {
            item.children[0].style.display = "";
        }

        return new Promise((res, rej) => {
            try {
                let tmp = new Image();
                tmp.onload = function() {
                    item.style.backgroundImage = 'url("' + url + '")';
                    item.style.opacity = "1";
                    res();
                }
                tmp.src = url;
            } catch (e) {
                rej(e);
            }
        })
    }

    (async () => {
        for (let item of document.getElementsByClassName("category-img")) {
            await sleep(50);

            let id = item.getAttribute("data-category-img");
            let data = JSON.parse(await (await window.fetch("https://derpibooru.org/api/v1/json/images/" + id)).text());
            let url = data['image']['representations'] ? (data['image']['representations']['small'] ?? data['image']['view_url']) : data['image']['view_url'];

            await showBackgroundImage(item, url, !data['image']['tags'].includes("safe"));
        }
    })();
</script>
<?php else: $category = $saved[$_GET["id"]];

if (!isset($category)) die();

?>
<div style="margin: 20px 50px 0;" class="main-app">
    <h2 <?= $_GET["id"] !== "favorites" ? "contenteditable" : "" ?> id="category-title"><?= $category["name"] ?></h2>

    <div style="margin-bottom: 10px;<?php if (!isset($category["owner"]) && (!$allowNsfw || (function_exists("proprietary_nsfw_7") && proprietary_nsfw_7()))): ?>display:none;<?php endif; ?>">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="show-nsfw" name="show-nsfw" disabled onchange="requestExplicit('refresh', true); toggleNSFW();">
            <label class="form-check-label" for="show-nsfw">Display NSFW images</label>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(6, 1fr); grid-gap: 10px;" id="grid">
        <?php foreach ($category["items"] as $id) { ?>
            <a style="cursor: pointer;" onclick="viewImage(<?= $id ?>)">
                <div class="card" style="height: calc((100vw - 150px) / 6);">
                    <div class="card-body" style="padding: 0; height: 100%; width: 100%; display: flex; align-items: center; justify-content: center;">
                        <div class="display-image" data-display-image="<?= $id ?>" style="background-size: cover; background-position: center; opacity: 0; transition: opacity 200ms; height: 100%; width: 100%; border-radius: 0.375rem;">
                            <div style="display: none;height: 100%; width: 100%; backdrop-filter: blur(10px); border-radius: 0.375rem;"></div>
                        </div>
                    </div>
                </div>
            </a>
        <?php } ?>
    </div>

    <script>
        let lastTitle = document.getElementById("category-title").innerHTML;

        setInterval(async () => {
            if (document.getElementById("category-title").innerHTML !== lastTitle) {
                let out = await (await window.fetch("/categories/rename.php?id=" + encodeURIComponent(`<?= $_GET["id"] ?>`) + "&name=" + encodeURIComponent(document.getElementById("category-title").innerText))).text();
                if (out.trim() !== "ok") throw new Error(out);
                lastTitle = document.getElementById("category-title").innerHTML;
            }
        }, 1000);

        function showBackgroundImage(item, url, blur) {
            if (blur) {
                item.children[0].style.display = "";
                item.classList.add("display-image-nsfw");
                item.parentElement.parentElement.parentElement.style.pointerEvents = "none";
                document.getElementById("show-nsfw").disabled = false;
            }

            return new Promise((res, rej) => {
                try {
                    let tmp = new Image();
                    tmp.onload = function() {
                        item.style.backgroundImage = 'url("' + url + '")';
                        item.style.opacity = "1";
                        res();
                    }
                    tmp.src = url;
                } catch (e) {
                    rej(e);
                }
            })
        }

        function toggleNSFW() {
            if (document.getElementById("show-nsfw").disabled) return;
            let enable = document.getElementById("show-nsfw").checked;

            if (enable) {
                Array.from(document.getElementsByClassName("display-image-nsfw")).forEach((item) => {
                    item.children[0].style.display = "none";
                    item.parentElement.parentElement.parentElement.style.pointerEvents = "";
                });
            } else {
                Array.from(document.getElementsByClassName("display-image-nsfw")).forEach((item) => {
                    item.children[0].style.display = "";
                    item.parentElement.parentElement.parentElement.style.pointerEvents = "none";
                });
            }
        }

        (async () => {
            for (let item of document.getElementsByClassName("display-image")) {
                await sleep(50);

                let id = item.getAttribute("data-display-image");
                let data = JSON.parse(await (await window.fetch("https://derpibooru.org/api/v1/json/images/" + id)).text());

                if (data['image']['hidden_from_users']) {
                    if (data['image']['duplicate_of']) {
                        await sleep(50);

                        let id = data['image']['duplicate_of'];
                        let data2 = JSON.parse(await (await window.fetch("https://derpibooru.org/api/v1/json/images/" + id)).text());

                        if (!data2['image']['hidden_from_users']) {
                            let url = data2['image']['representations'] ? (data2['image']['representations']['small'] ?? data2['image']['view_url']) : data2['image']['view_url'];

                            await showBackgroundImage(item, url, !data2['image']['tags'].includes("safe"));
                        }
                    }
                } else {
                    let url = data['image']['representations'] ? (data['image']['representations']['small'] ?? data['image']['view_url']) : data['image']['view_url'];

                    await showBackgroundImage(item, url, !data['image']['tags'].includes("safe"));
                }
            }
        })();
    </script>
</div>
<?php endif; ?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
