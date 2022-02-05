<?php

abstract class Model
{
    protected function dbConnectPDO()
    {
		try{
			$db = new PDO('mysql:host=localhost;dbname=nvs;charset=utf8', 'root', 'yolo');
			return $db;
		}
		catch(Exception $e) {
			die('Erreur : '.$e->getMessage());
		}
    }
}