<?php global $userName; ?>
<div class="modal" id="explicit-modal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" style="text-align: center;">
                <img alt="" style="width: 64px; height: 64px;" src="/assets/explicit.svg">
                <h3>This content is sexually explicit</h3>

                <p>This page shows uncensored graphically explicit sexual content that you may not want to see in some cases. Please refrain from visiting this part of the website in a public place.</p>
                <p>By continuing, you agree to be presented with sexually explicit content that is not appropriate for everyone.</p>

                <span onclick="explicitConfirm();" id="explicit-modal-confirm" class="btn btn-primary">Continue</span>
                <span onclick="explicitCancel();" id="explicit-modal-cancel" class="btn btn-outline-secondary">Go back</span>

                <label style="margin-top:10px; display: block; text-align: left; opacity: .5;">
                    <input checked type="checkbox" class="form-check-input" id="explicit-modal-hour">
                    Don't show for the next hour
                </label>
            </div>
        </div>
    </div>
</div>

<style>
    #explicit-modal .modal-header {
        border-bottom: 1px solid #353738;
    }

    #explicit-modal .modal-content {
        border: 1px solid rgba(255, 255, 255, .2);
        background-color: #111;
    }
</style>

<!--suppress JSVoidFunctionReturnValueUsed -->
<script>
    window.explicitModal = new bootstrap.Modal(document.getElementById("explicit-modal"));
    <?php if (file_exists("/peh/metadata")): ?>
    window.ip = "<?= $_SERVER['HTTP_X_FORWARDED_FOR'] ?>";
    window.front = "<?php

    $front = [];
    $id = null;

    if ($userName === "raindrops") {
        $id = "gdapd";
    } else if ($userName === "cloudburst") {
        $id = "ynmuc";
    }

    $fronters = json_decode(file_get_contents("/peh/$id/fronters.json"), true)["members"];
    $front = array_map(function ($i) {
        return $i["id"];
    }, $fronters);

    echo(implode(",", $front));

    ?>";
    window.age = <?php

    if (isset($front[0]) && file_exists("/peh/metadata/" . $front[0] . ".json")) {
        $metadata = json_decode(file_get_contents("/peh/metadata/" . $front[0] . ".json"), true);
        $age = null;

        if (isset($metadata["birth"]["age"]) && $metadata["birth"]["age"] !== 0) {
            $age = $metadata["birth"]["age"];
        } else if (isset($metadata["birth"]["year"]) && $metadata["birth"]["year"] > 1990) {
            $age = (int)date('Y') - $metadata["birth"]["year"] + (strtotime(date('Y') . "-" . $metadata["birth"]["date"]) <= time() ? 0 : -1);
        }

        if (is_string($age) && isset(explode("-", $age)[1]) && is_numeric(explode("-", $age)[1])) {
            $age = (int)explode("-", $age)[1];
        }

        echo($age);
    }

    ?>;
    window.allowExplicit = true;
    <?php else: ?>
    window.allowExplicit = false;
    <?php endif; ?>
    window.explicitCancelAction = "back";

    function requestExplicit(ifNotAgreed, allowUnderage) {
        window.explicitCancelAction = ifNotAgreed;

        if (!window.allowExplicit || (!allowUnderage && (window.age < 15 || !window.age))) {
            document.getElementById("explicit-modal-confirm").classList.add("disabled");
            document.getElementById("explicit-modal-hour").disabled = true;

            window.explicitModal.show();
            document.getElementById("explicit-modal").classList.add("fade");
            return;
        }

        if (!localStorage.getItem("explicit-consent")) {
            window.explicitModal.show();
            document.getElementById("explicit-modal").classList.add("fade");
        } else {
            let parts = localStorage.getItem("explicit-consent").split("|");

            if (parts[0] !== window.front || parts[1] !== window.ip || new Date().getTime() - parseInt(parts[2]) > 3600000) {
                window.explicitModal.show();
                document.getElementById("explicit-modal").classList.add("fade");
            }
        }
    }

    function explicitConfirm() {
        window.explicitModal.hide();

        if (document.getElementById("explicit-modal-hour").checked) {
            localStorage.setItem("explicit-consent", window.front + "|" + window.ip + "|" + new Date().getTime());
        }
    }

    function explicitCancel() {
        if (window.explicitCancelAction === "refresh") {
            location.reload();
        } else {
            if (history.length > 1) {
                if (history.back() === undefined) {
                    location.href = "https://booru.equestria.dev";
                }
            } else {
                location.href = "https://booru.equestria.dev";
            }
        }
    }
</script>