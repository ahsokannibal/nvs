<?php
require_once("Model.php");

class Carte extends Model
{
	public $x_carte;
	public $y_carte;
	public $occupee_carte;
	public $fond_carte;
	public $idPerso_carte;
	public $image_carte;
	public $vue_nord;
	public $vue_sud;
	public $coordonnees;
	public $vue_nord_date;
	public $vue_sud_date;

	public function __set($name, $value) {}
	
	public function __get($name){
		return $name;
	}
	
	public function recupereVoisins($id_cible, $x_cible, $y_cible){
		$db = $this->dbConnectPDO();
		$sql = "SELECT idPerso_carte FROM carte WHERE x_carte >= $x_cible - 1 AND x_carte <= $x_cible + 1 AND y_carte >= $y_cible - 1 AND y_carte <= $y_cible + 1 AND occupee_carte = '1' AND idPerso_carte != '$id_cible'";
		$request = $db->prepare($sql);
		$request->execute();
		return $request;
	}
}
