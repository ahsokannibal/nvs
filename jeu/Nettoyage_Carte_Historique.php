<?php
require_once($_SERVER['DOCUMENT_ROOT']."/mvc/Db/Db.php");
$db = Db::getInstance();
$query = "delete FROM `carte_historique` WHERE carte_date < DATE_SUB(DATE_FORMAT(now(), '%Y-%m-%d'), INTERVAL 2 MONTH)";

$request = $db->prepare($query);
$request->execute();
?>