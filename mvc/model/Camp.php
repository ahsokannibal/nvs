<?php
require_once("Model.php");

class Camp extends Model
{
	private $id;
	private	$name;
	private $desc;
	private $color;

	/*
	 * récupérer les infos du camp
	 *
	*/
	// cette fonction devra utiliser à terme la table camp qui n'existe pas encore
    public function getCamp($id){
		switch($id){
			case 1:
				$camp = [
					'id' => 1,
					'name' => 'Nord',
					'desc' => "Armée de l'union",
					'color' => "blue"
				];
				break;
			case 2:
				$camp = [
					'id' => 2,
					'name' => 'Sud',
					'desc' => "Armée des Etats confédérés",
					'color' => "red"
				];
				break;
			default:
				$camp = [
					'id' => 0,
					'name' => 'Sans patrie',
					'desc' => "Armée de mercenaire",
					'color' => "grey"
				];
			
		}
		return $camp;
	}
}