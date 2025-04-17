<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $allowNsfw;

header("Content-Type: image/png");
if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/logo2.png")) die(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/logo2.png"));