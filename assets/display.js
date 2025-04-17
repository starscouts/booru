function showBackgroundImage(item, url, blur) {
    if (blur) {}

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
    let images = JSON.parse(await (await window.fetch(`https://derpibooru.org/api/v1/json/search/images/?q=${encodeURIComponent(_display_filter ?? "-*")}&filter_id=56027&per_page=50&page=${_display_page ?? 1}`)).text());

    let dom = "";

    for (let image of images['images']) {
        if (image['processed'] && !image['animated']) {
            dom += `
                    <a style="cursor: pointer;" onclick="viewImage(${image['id']});">
                        <div class="card" style="height: calc((100vw - 150px) / 6);">
                            <div class="card-body" style="padding: 0; height: 100%; width: 100%; display: flex; align-items: center; justify-content: center;">
                                <div class="display-image" data-display-image="${image['id']}" style="background-size: cover; background-position: center; opacity: 0; transition: opacity 200ms; height: 100%; width: 100%; border-radius: 0.375rem;"></div>
                            </div>
                        </div>
                    </a>`;
        }
    }

    if (images['images'].length === 0) dom = "No matching images were found.";

    new Promise(async () => {
        for (let image of images['images']) {
            await (await window.fetch("/pushTags.php?tag=" + encodeURIComponent(image['tags'].join(",")))).text();
        }
    });

    document.getElementById("grid").innerHTML = dom;
    document.getElementById("pagination").style.display = "";

    for (let item of document.getElementsByClassName("display-image")) {
        await sleep(50);

        let id = item.getAttribute("data-display-image");
        let data = JSON.parse(await (await window.fetch("https://derpibooru.org/api/v1/json/images/" + id)).text());
        let url = data['image']['representations'] ? (data['image']['representations']['small'] ?? data['image']['view_url']) : data['image']['view_url'];

        await showBackgroundImage(item, url, !data['image']['tags'].includes("safe"));
    }
})();