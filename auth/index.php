<?php

header("Location: https://auth.equestria.horse/hub/api/rest/oauth2/auth?client_id=" . json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/oauth.json"), true)["id"] . "&response_type=code&redirect_uri=https://booru.equestria.dev/auth/callback&scope=Hub&request_credentials=default&access_type=offline");
die();