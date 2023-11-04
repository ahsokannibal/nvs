<?php
require_once("Model.php");

class Item extends Model
{
	protected $table = "objet";
	protected $primaryKey = "id_objet";
	// protected $fillable = [];
	protected $guarded = [];
	
	/**
	* Fonction qui permet de supprimer un ticket de train
	* @param $id_perso : L'identifiant du personnage qui possède le billet
	* @param $destination : L'identifiant de la gare d'arrivée
	* @return Bool
	*/
	public function supprimerTicketTrain($id_perso,$destination){
		$db = $this->dbConnectPDO();
		
		$query = "DELETE FROM perso_as_objet WHERE id_objet='1' AND id_perso=:id_perso AND capacite_objet=:destination LIMIT 1";
		$request = $db->prepare($query);
		$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
		$request->bindParam('destination', $destination);
		$result = $request->execute();
		
		if($result > 0){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	* Récupère les infos d'un objet
	* @param $id : L'identifiant du type d'objet
	* @param $attributs : quelles colonnes doit-on récupérer
	* @return obj
	*/
	public function getItem($id,$attributs = []){
		$db = $this->dbConnectPDO();
		
		if($attributs){
			$attributs = implode(', ',$attributs);
		}else{
			$attributs = "*";
		}
		
		$query = 'SELECT '.$attributs.' FROM objet WHERE id_objet=:id';
		
		$request = $db->prepare($query);
		$request->bindParam('id', $id, PDO::PARAM_INT);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_OBJ);
		$result = $request->fetch();

		return $result;
	}
	
	/**
	* Vérifie si l'objet peut être équipé par ce type de personnage
	* @param $type_perso : L'identifiant du type d'objet
	* @param $type_obj : L'identifiant du type d'objet
	* @return bool
	*/
	public function canBeEquiped(INT $type_perso,INT $type_obj){
		$db = $this->dbConnectPDO();
		
		$query = 'SELECT * FROM objet_as_type_unite WHERE id_objet=:type_obj AND id_type_unite=:type_perso';
		
		$request = $db->prepare($query);
		$request->bindParam('type_obj', $type_obj, PDO::PARAM_INT);
		$request->bindParam('type_perso', $type_perso, PDO::PARAM_INT);
		$request->execute();
		$result = $request->fetch();

		return $result;
	}
}