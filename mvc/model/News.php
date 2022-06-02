<?php
require_once("Model.php");

class News extends Model
{
	function getNews(){
		$db = $this->dbConnectPDO();
		
		$query = 'SELECT date, contenu FROM news ORDER BY date DESC LIMIT 10';
		
		$request = $db->prepare($query);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_OBJ);
		$result = $request->fetchAll();

		return $result;
	}
}