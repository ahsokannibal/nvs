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

	public function putEventAttaque($id, $couleur_clan_perso, $nom_perso, $attaque_str, $id_cible, $couleur_clan_cible, $nom_cible, $touche, $precision_final, $degats_final, $gain_xp, $gain_pc){
		$db = $this->dbConnectPDO();
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a $attaque_str ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' ( Précision : $touche / $precision_final ; Dégâts : $degats_final ; Gain XP : $gain_xp ; Gain PC : $gain_pc )',NOW(),'0')";
		$request = $db->prepare($sql);
		$request->execute();
	}
}
