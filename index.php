<?php
// /proje1/index.php
session_start();

$SESSION_KEY_AUTH = 'auth';

// Giriş yapılmamışsa login.php'ye yönlendir
if (empty($_SESSION[$SESSION_KEY_AUTH])) {
    header("Location: /proje8/login.php");
    exit;
}

// Giriş yapılmışsa doğrudan bot_panel.php'ye yönlendir
header("Location: /proje8/bot_panel.php");
exit;
