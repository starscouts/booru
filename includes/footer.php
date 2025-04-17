<div class="modal fade" id="image-preview">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Image viewer</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="image-preview-container">
                -
            </div>
        </div>
    </div>
</div>

<script>
    let tags = JSON.parse(atob(`<?= base64_encode(json_encode(json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tags.json"))->db)) ?>`)) ?? {};
    let categories = JSON.parse(atob(`<?= base64_encode(json_encode(json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/data/tags.json"))->categories)) ?>`)) ?? {};

    function timeAgo(time) {
        if (!isNaN(parseInt(time))) {
            time = new Date(time).getTime();
        }

        let periods = ["seconde", "minute", "hour", "day", "week", "month", "year", "age"];
        let lengths = ["60", "60", "24", "7", "4.35", "12", "100"];

        let now = new Date().getTime();

        let difference = Math.round((now - time) / 1000);
        let tense;
        let period;

        if (difference <= 10 && difference >= 0) {
            return "now";
        } else if (difference > 0) {
            tense = "ago";
        } else {
            tense = "later";
        }

        let j;

        for (j = 0; difference >= lengths[j] && j < lengths.length - 1; j++) {
            difference /= lengths[j];
        }

        difference = Math.round(difference);

        period = periods[j];

        return `${difference} ${period}${difference > 1 ? 's' : ''} ${tense}`;
    }

    function titleCase(str) {
        let splitStr = str.toLowerCase().split(' ');
        for (let i = 0; i < splitStr.length; i++) {
            splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
        }
        return splitStr.join(' ');
    }

    async function saveItem(category, id) {
        document.getElementById("dropdown-save").disabled = true;
        document.getElementById("dropdown-save-spinner").style.display = "";
        document.getElementById("dropdown-save-menu").classList.remove("show");

        try {
            let out = await (await window.fetch("/categories/save.php?category=" + encodeURIComponent(category) + "&id=" + encodeURIComponent(id))).text();
            if (out.trim() !== "ok") throw new Error(out);

            document.getElementById("dropdown-save-outer").style.display = "none";
            document.getElementById("dropdown-remove").style.display = "";
        } catch (e) {
            console.error(e);
        }

        document.getElementById("dropdown-save").disabled = false;
        document.getElementById("dropdown-save-spinner").style.display = "none";
    }

    async function removeSaved(id) {
        document.getElementById("dropdown-remove").disabled = true;
        document.getElementById("dropdown-remove-spinner").style.display = "";

        try {
            let out = await (await window.fetch("/categories/unsave.php?id=" + encodeURIComponent(id))).text();
            if (out.trim() !== "ok") throw new Error(out);

            document.getElementById("dropdown-save-outer").style.display = "";
            document.getElementById("dropdown-remove").style.display = "none";
        } catch (e) {
            console.error(e);
        }

        document.getElementById("dropdown-remove").disabled = false;
        document.getElementById("dropdown-remove-spinner").style.display = "none";
    }

    function viewImage(id, dontShow) {
        document.getElementById("image-preview-container").innerText = "Loading...";
        if (!dontShow) new bootstrap.Modal(document.getElementById("image-preview")).show();

        try {
            window.fetch("https://derpibooru.org/api/v1/json/images/" + id).then(async (res) => {
                let isSaved = JSON.parse(await (await window.fetch("/categories/isSaved.php?id=" + id)).text())["value"];
                let saveCategories = JSON.parse(await (await window.fetch("/categories/get.php")).text());

                res.text().then((data) => {
                    try {
                        marked.setOptions({ smartypants: true });

                        data = JSON.parse(data)["image"];
                        console.log(data);

                        document.getElementById("image-preview-container").innerHTML = `
                        <div class="dropdown" id="dropdown-save-outer" ${isSaved ? `style="display:none;"` : ""}>
                            <button id="dropdown-save" type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                <span class="spinner-border spinner-border-sm" id="dropdown-save-spinner" style="display: none;"></span>
                                Save
                            </button>
                            <ul class="dropdown-menu" id="dropdown-save-menu">
                                <li><a class="dropdown-item" href="#image-preview" onclick="saveItem('favorites', '${data['id']}')">Favorites</a></li>
                                ${saveCategories.length > 1 ? `<li><hr class="dropdown-divider"></li>` : ``}
                                ${saveCategories.map(i => `<li><a class="dropdown-item ${!i.enabled ? "disabled" : ""}" href="#image-preview" onclick="saveItem('${i.id}', '${data['id']}')">${i.name}</a></li>`).join('')}
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#image-preview" onclick="saveItem('new', '${data['id']}')">New category</a></li>
                            </ul>
                        </div>
                        <button type="button" onclick="removeSaved('${data['id']}')" class="btn btn-outline-success" id="dropdown-remove" ${!isSaved ? `style="display:none;"` : ""}>
                            <span class="spinner-border spinner-border-sm" id="dropdown-remove-spinner" style="display: none;"></span>
                            Unsave
                        </button>
                        <img src="${data['representations']['full'] ?? data['view_url']}" style="margin-top: 10px; max-height: 75vh; max-width: 100%; margin-left: auto; margin-right: auto; display: block;">
                        ${data['description'] ? `<hr><p>${marked.parse(data['description']).replace(/&gt;&gt;(\d+)(s|t|p|)/gm, '<a href="#image-preview" onclick="viewImage($1, true);">$1</a>')}</p>` : ""}
                        <hr>
                        <table>
                            <tbody>
                                <tr>
                                    <td style="padding-right: 10px; text-align: right;"><b>Tags:</b></td>
                                    <td>${data['tags'].filter(tag => !tag.includes(":")).map((tag) => { let info = tags[tag] ?? {}; return `<a class="tag" style="color:rgb(${info ? (info['category'] && categories[info['category']] ? categories[info['category']] : '0,0,0') : '0,0,0'});" href="/tag/?id=${encodeURIComponent(tag)}"><span style='display: inline-block; margin-right:10px; background-color: rgba(${info ? (info['category'] && categories[info['category']] ? categories[info['category']] : '0,0,0') : '0,0,0'},0.1); border: 1px solid rgba(${info ? (info['category'] && categories[info['category']] ? categories[info['category']] : '0,0,0') : '0,0,0'},0.5); padding: 2px 10px; margin-bottom: 2px; border-radius: 999px;'>${info['display_name'] ? info['display_name'] : titleCase(tag)}</span></a>`; }).join("")}</td>
                                </tr>
                                <tr>
                                    <td style="padding-right: 10px; text-align: right;"><b>Uploaded:</b></td>
                                    <td>${timeAgo(data['created_at'])}</td>
                                </tr>
                                <tr>
                                    <td style="padding-right: 10px; text-align: right;"><b>Uploader:</b></td>
                                    <td>${data['uploader'] ? `<a href="https://derpibooru.org/profiles/${data['uploader']}" target="_blank">${data['uploader']}</a>` : `Anonymous`}</td>
                                </tr>
                                <tr>
                                    <td style="padding-right: 10px; text-align: right;"><b>Source:</b></td>
                                    <td><a target="_blank" href="${data['source_url'] ?? 'https://derpibooru.org/images/' + data['id']}">View</a></td>
                                </tr>
                                <tr>
                                    <td style="padding-right: 10px; text-align: right;"><b>ID:</b></td>
                                    <td><code>${data['id']}</code></td>
                                </tr>
                                <tr>
                                    <td style="padding-right: 10px; text-align: right;"><b>Share:</b></td>
                                    <td><a href="#" onclick="navigator.clipboard.writeText('https://derpibooru.org/images/${data['id']}')">Copy link</a></td>
                                </tr>
                            </tbody>
                        </table>
                        `;
                    } catch (e) {
                        console.error(e);
                        document.getElementById("image-preview-container").innerText = "An internal error occurred while trying to load this image, please try again later.";
                    }
                })
            })
        } catch (e) {
            console.error(e);
            document.getElementById("image-preview-container").innerText = "An internal error occurred while trying to load this image, please try again later.";
        }
    }
</script>

<br><br>

</body>
</html>