<?php
session_start();

function require_login() {
    if (empty($_SESSION['auth'])) {
        header("Location: login.php");
        exit;
    }
}
