<?php $title = "Followed"; require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php"; global $userName;

$follows = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/follows.json"), true)[$userName];
$tags = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tags.json"), true);

?>

<div style="margin: 20px 50px 0;" class="main-app">
    <div class="list-group">
        <?php foreach ($follows as $tag): ?>
            <a href="/tag/?id=<?= $tag ?>" class="list-group-item list-group-item-action tag-item" style="display: grid; grid-template-columns: 5vw 1fr; grid-gap: 15px;" data-tag="<?= $tag ?>">
                <div style="background-color: rgba(255, 255, 255, .1); height: 64px; border-radius: 5px;">
                    <div style="opacity: 0; transition: opacity 200ms; background-size: cover; background-position: center; height: 100%; width: 100%; border-radius: 5px;">
                        <div style="display:none;width: 100%;backdrop-filter: blur(5px);height: 100%;border-radius: 5px;"></div>
                    </div>
                </div>
                <div style="display: flex; align-items: center;">
                    <div>
                        <b><?= isset($tags["db"][$tag]) && isset($tags["db"][$tag]["display_name"]) ? $tags["db"][$tag]["display_name"] : ucwords(strip_tags($tag)) ?></b>
                        <div></div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
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
        for (let item of document.getElementsByClassName("tag-item")) {
            await sleep(50);

            let id = item.getAttribute("data-tag");
            let data = JSON.parse(await (await window.fetch("https://derpibooru.org/api/v1/json/tags/" + encodeURIComponent(id).replaceAll("%20", "+"))).text());

            item.children[1].children[0].children[1].innerText = formatNumber(data['tag']['images']) + " image" + (data['tag']['images']> 0 ? "s" : "");

            let imgId = parseInt(data['tag']["description"].replace(/([^>]*)>>(\d+)(.*)/gm, "$2"));

            let imgData;
            if (!isNaN(imgId)) {
                imgData = JSON.parse(await (await window.fetch("https://derpibooru.org/api/v1/json/images/" + imgId)).text());
            } else {
                imgData = {
                    image: JSON.parse(await (await window.fetch("https://derpibooru.org/api/v1/json/search/images/?q=" + encodeURIComponent(id).replaceAll("%20", "+") + "&sf=wilson_score&sd=desc")).text())['images'].filter(i => !i['animated'])[0]
                };
            }

            let url = imgData['image']['representations'] ? (imgData['image']['representations']['small'] ?? imgData['image']['view_url']) : imgData['image']['view_url'];

            await showBackgroundImage(item.children[0].children[0], url, !imgData['image']['tags'].includes("safe"));
        }
    })();
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
