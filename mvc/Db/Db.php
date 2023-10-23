<?php
require_once($_SERVER['DOCUMENT_ROOT']."/jeu/config.php");

class Db extends PDO
{
	//instance unique de la classe
	private static $instance;

	private const DB_HOST = BDD_HOST;
	private const DB_USER = BDD_LOGIN;
	private const DB_PASS = BDD_PASSWORD;
	private const DB_NAME = BDD_NAME;

	private function __construct()
	{
		$dsn = 'mysql:host='.self::DB_HOST.';dbname='.self::DB_NAME.';charset=utf8';
		try{
			parent::__construct($dsn, self::DB_USER, self::DB_PASS);
			
		}catch(PDOexception $e){
			die($e->getMessage());
		}
	}
	
	public static function getInstance()
	{
		if(self::$instance === null){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
}