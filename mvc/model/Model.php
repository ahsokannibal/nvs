<?php

require_once('../jeu/config.php');

abstract class Model
{
    protected function dbConnectPDO()
    {
		try{
			$db = new PDO('mysql:host=localhost;dbname=nvs;charset=utf8', BDD_LOGIN, BDD_PASSWORD);
			return $db;
		}
		catch(Exception $e) {
			die('Erreur : '.$e->getMessage());
		}
    }
}
