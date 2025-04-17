<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php"; global $allowNsfw;
if (!$allowNsfw) header("Location: /nsfw/home") and die();

if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/app3-v2.php")) require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/proprietary/app4-v2.php";
