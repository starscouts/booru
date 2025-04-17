<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $allowNsfw;
if (!$allowNsfw) header("Location: /nsfw/home") and die();

if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/app1-v2.php")) require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/app1-v2.php";
