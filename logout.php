<?php
session_start();

$_SESSION = [];
session_unset();
session_destroy();

header("Location: login.php");
var_dump(password_verify($password, $user['password']));
exit();

