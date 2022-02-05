<?php
require_once("Model.php");

class Event extends Model
{
	private $IDActeur_evenement;
	private	$nomActeur_evenement;
	private $phrase_evenement;
	private $IDCible_evenement;
	private $nomCible_evenement;
	private $effet_evenement;
	private $date_evenement;
	private $special;
	
    // private $perso_id;
	// private $event;
	// private $target_id;
	// private $effect;
	// private $created_at;
	// private $special;
	
	public function __set($name, $value) {}
	
	public function __get($name){
		return $name;
	}
	
	public function createEvent(){
		$db = $this->dbConnectPDO();
		// SQL à préparer et executer
		return "fonction d'enregistrement à programmer";
	}
	
	public function getUserEvents($id){
		$db = $this->dbConnectPDO();
		
		$query = 'SELECT * FROM evenement WHERE IDActeur_evenement=:perso_id';
		
		$request = $db->prepare($query);
		$request->bindParam('perso_id', $id, PDO::PARAM_INT);
		$request->execute();

		return $request;
	}
}