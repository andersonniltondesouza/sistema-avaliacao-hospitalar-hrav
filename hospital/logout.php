<?php
require 'auth.php';
$_SESSION = [];
session_destroy();
header('Location: login_page.php');
exit;
