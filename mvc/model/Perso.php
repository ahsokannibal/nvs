<?php
require_once("Model.php");

class Perso extends Model
{
	public $id_perso;
	public $idJoueur_perso;
	public $nom_perso;
	public $type_perso;
	public $x_perso;
	public $y_perso;
	public $xp_perso;
	public $pi_perso;
	public $pc_perso;
	public $or_perso;
	public $pvMax_perso;
	public $pm_perso;
	public $pmMax_perso;
	public $pv_perso;
	public $perception_perso;
	public $recup_perso;
	public $pa_perso;
	public $paMax_perso;
	public $protec_perso;
	public $charge_perso;
	public $chargeMax_perso;
	public $bonusPerception_perso;
	public $bonusRecup_perso;
	public $bonusPM_perso;
	public $bonusPA_perso;
	public $bonus_perso;
	public $image_perso;
	public $message_perso;
	public $bourre_perso;
	public $nb_kill;
	public $nb_mort;
	public $nb_pnj;
	public $dateCreation_perso;
	public $DLA_perso;
	public $description_perso;
	public $clan;
	public $a_gele;
	public $est_gele;
	public $date_gele;
	public $chef;
	public $bataillon;
	public $convalescence;
	public $genie;
	public $gain_xp_tour;

	public function __set($name, $value) {}

	public function __get($name){
		return $this->$name;
	}

	public function persoExist($id){
		$db = $this->dbConnectPDO();

		$query = 'SELECT COUNT(*) FROM perso WHERE id_perso=:id';

		$request = $db->prepare($query);
		$request->bindParam('id', $id, PDO::PARAM_INT);
		$request->execute();
		$result = (boolean) $request->fetchColumn();

		return $result;
	}

	/**
	* Récupère les infos du perso
	* @param $id_perso : L'identifiant du personnage
	* @param $attributs : quelles colonnes doit-on récupérer
	* @return Perso
	*/
	public function getPerso($id,$attributs = []){
		$db = $this->dbConnectPDO();

		if($attributs){
			$attributs = implode(', ',$attributs);
		}else{
			$attributs = "*";
		}

		$query = 'SELECT '.$attributs.' FROM perso WHERE id_perso=:id';

		$request = $db->prepare($query);
		$request->bindParam('id', $id, PDO::PARAM_INT);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_CLASS,get_class($this));
		$result = $request->fetch();

		return $result;
	}
	
public function getAllPerso($attributs = [],$camp=null){
		$db = $this->dbConnectPDO();

		if($attributs){
			$attributs = implode(', ',$attributs);
		}else{
			$attributs = "*";
		}
		
		if($camp){
			$query = 'SELECT '.$attributs.' FROM perso WHERE clan=:camp';
			$request = $db->prepare($query);
			$request->bindParam('camp', $camp, PDO::PARAM_INT);
		}else{
			$query = 'SELECT '.$attributs.' FROM perso';
			$request = $db->prepare($query);
		}

		$request->execute();
		$result = $request->fetchAll();

		return $result;
	}

	/**
	* Récupère tous les objets/armes détenus par le perso
	* @param $id_perso : L'identifiant du personnage
	* @param $type : quel type d'objet on veut récuperer 'item' pour les objets courants ou 'weapon' pour les armes non équipées
	* @return array
	*/
	public function items($id_perso,$type){
		$db = $this->dbConnectPDO();

		switch($type){
			case 'item':
				$query = "SELECT objet.id_objet, nom_objet, poids_objet, description_objet, type_objet, image_objet, contient_alcool, echangeable, deposable, GROUP_CONCAT(perso_as_objet.capacite_objet SEPARATOR ',') as destinations, COUNT(*) AS quantity, SUM(poids_objet) as poids_total, SUM(perso_as_objet.equip_objet) as equiped FROM objet INNER JOIN perso_as_objet ON perso_as_objet.id_perso=:id_perso AND perso_as_objet.id_objet=objet.id_objet GROUP BY perso_as_objet.id_objet";
				break;
			case 'weapon':
				$query = "SELECT arme.id_arme, nom_arme, poids_arme, description_arme, image_arme, COUNT(*) AS quantity, SUM(poids_arme) as poids_total FROM arme INNER JOIN perso_as_arme ON perso_as_arme.id_perso=:id_perso AND perso_as_arme.id_arme=arme.id_arme AND perso_as_arme.est_portee='0' GROUP BY id_arme";
				break;
		}

		$request = $db->prepare($query);
		$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
		$request->execute();
		$result = $request->fetchAll();

		return $result;
	}


	/**
	* On utilise un objet de l'inventaire d'un perso et on applique les bonus
	* @param $perso : instance de classe Perso
	* @param $item : instance de classe Item
	* @return bool
	*/
	public function useItem($perso,$item,$equip=true){
		$db = $this->dbConnectPDO();

		$id_perso = $perso->id_perso;
		$pv_perso = $perso->pv_perso;
		$pvMax_perso = $perso->pvMax_perso;
		$pm_perso = $perso->pm_perso;
		$pmMax_perso = $perso->pmMax_perso;
		$pa_perso = $perso->pa_perso;
		$paMax_perso = $perso->paMax_perso;

		$id_item = $item->id_objet;
		$nom_objet = $item->nom_objet;
		$type_objet = $item->type_objet;
		$cout_pa = $item->coutPa_objet;
		$poids = $item->poids_objet;
		$bonusPerception = $item->bonusPerception_objet;
		$bonusRecup = $item->bonusRecup_objet;
		$bonusPv = $item->bonusPv_objet;
		$bonusPm = $item->bonusPm_objet;
		$bonusPa = $item->bonusPA_objet;
		$alcool = $item->contient_alcool;

		// fonction d'ajustement si le bonus dépasse le maximum de l'attribut du perso
		function checkMax($actual,$bonus,$maximum){
			if($actual+$bonus>$maximum){
				$bonus = $bonus-($actual+$bonus-$maximum);
			}
			return $bonus;
		}

		$bonusPv = checkMax($pv_perso,$bonusPv,$pvMax_perso);
		// $bonusPm = checkMax($pm_perso,$bonusPm,$pmMax_perso);
		// $bonusPa = checkMax($pa_perso,$bonusPa,$paMax_perso);


		if($type_objet == 'N'){
			//suppression de l'objet de l'inventaire
			$query = "DELETE FROM perso_as_objet WHERE id_perso=:id_perso AND id_objet=:id_item LIMIT 1";
		}elseif($type_objet == 'E'){
			$poids = 0;
			if($equip==false){
				//déséquipe l'objet
				$query = "UPDATE perso_as_objet SET equip_objet='0' WHERE id_perso=:id_perso AND id_objet=:id_item AND equip_objet=1 LIMIT 1";
			}else{
				//équipe l'objet
				$query = "UPDATE perso_as_objet SET equip_objet='1' WHERE id_perso=:id_perso AND id_objet=:id_item LIMIT 1";
			}
		}else{
			return false;
		}

		$request = $db->prepare($query);
		$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
		$request->bindParam('id_item', $id_item, PDO::PARAM_INT);
		$result = $request->execute();

		if($result){

			// MAJ des PA, du poids et des bonus du perso
			$query = "UPDATE perso SET pa_perso = pa_perso-:pa, charge_perso=charge_perso-:poids, pv_perso = pv_perso+:pv, bonusPerception_perso=bonusPerception_perso+:bonusPerc, bonusRecup_perso=bonusRecup_perso+:bonusRecup, bonusPM_perso=bonusPM_perso+:bonusPM, bonusPA_perso=bonusPA_perso+:bonusPA, bourre_perso=bourre_perso+:alcool WHERE id_perso=:id_perso";
			$request = $db->prepare($query);
			$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
			$request->bindParam('pa', $cout_pa, PDO::PARAM_INT);
			$request->bindParam('poids', $poids);
			$request->bindParam('pv', $bonusPv, PDO::PARAM_INT);
			$request->bindParam('bonusPerc', $bonusPerception, PDO::PARAM_INT);
			$request->bindParam('bonusRecup', $bonusRecup, PDO::PARAM_INT);
			$request->bindParam('bonusPM', $bonusPm, PDO::PARAM_INT);
			$request->bindParam('bonusPA', $bonusPa, PDO::PARAM_INT);
			$request->bindParam('alcool', $alcool, PDO::PARAM_INT);
			$result = $request->execute();

			if($result){
				return true;
			}else{
				return false;
			}

		}else{
			return false;
		}
	}

	/**
	* Déséquipe une arme d'un perso
	* @param $id_perso : L'identifiant du perso
	* @param $attribut : quel attribut on modifie. Doit être le même que dans la table Perso
	* @param $effect : Le type d'effet : bonus ou malus
	* @return bool
	*/
	public function equipItem($id_perso,$id_obj,$equip=true){
		$db = $this->dbConnectPDO();

		$query = "UPDATE perso_as_objet SET equip_objet='0' WHERE id_perso=:id_perso AND id_objet=:id_obj AND equip_objet=1 LIMIT 1";
		$request = $db->prepare($query);
		$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
		$request->bindParam('id_obj', $id_obj, PDO::PARAM_INT);
		$result = $request->execute();

		if($result){
			return true;
		}else{
			return false;
		}
	}

	/**
	* Ajoute un bonus/malus à un perso
	* @param $id_perso : L'identifiant du perso
	* @param $attribut : quel attribut on modifie. Doit être le même que dans la table Perso
	* @param $effect : Le type d'effet : bonus ou malus
	* @return bool
	*/
	public function addBonusOrMalus($id_perso,$attribut,$effect){
		$db = $this->dbConnectPDO();

		$query = 'UPDATE perso SET '.$attribut.'='.$attribut.'+:effect WHERE id_perso=:id_perso';
		$request = $db->prepare($query);
		$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
		$request->bindParam('effect', $effect, PDO::PARAM_INT);
		$result = $request->execute();

		if($result){
			return true;
		}else{
			return false;
		}
	}

	public function infligeDegats($id_cible, $degats_final){
		$db = $this->dbConnectPDO();
		// mise a jour des pv et des malus de la cible
		$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_final, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible'";
		$request = $db->prepare($sql);
		$request->execute();
	}

	public function perso_gain_xp($id, $gain_xp){
		$db = $this->dbConnectPDO();
		$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp WHERE id_perso='$id'";
		$request = $db->prepare($sql);
		$request->execute();
	}
}
