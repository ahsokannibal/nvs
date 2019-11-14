<?php
session_start(); // On appelle la session
$_SESSION = array(); // On écrase le tableau de session
session_destroy(); // On détruit la session

header ("Location:index.php");
?>
