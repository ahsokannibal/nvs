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

	/**
     * Vérifie que le perso existe
     *
     * @return bool
     */
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
     *
     * @return class Perso
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
	
	/**
     * Récupère le nombre de personnages actifs
     *
     * @return int
     */
	 public function countPersos($camp=false){
		$db = $this->dbConnectPDO();
		
		if($camp){
			$query = 'SELECT COUNT(*) FROM perso WHERE est_gele="0" AND clan=:camp';
		}else{
			$query = 'SELECT COUNT(*) FROM perso WHERE est_gele="0"';
		}

		$request = $db->prepare($query);
		$request->bindParam('camp', $camp, PDO::PARAM_INT);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_NUM);
		$result = $request->fetch();

		return $result[0];
	 }
	
	/**
     * Savoir si l'utilisateur d'un personnage est admin, anim, ou rédacteur
     *
     * @return bool
     */
	public function is($id, $profil){
		$db = $this->dbConnectPDO();

		switch($profil){
			case 'admin':
				$profil = "admin_perso";
				break;
			case 'anim':
				$profil = "animateur";
				break;
			case 'redac':
				$profil = "redacteur";
				break;
			default:
		}
		$query = 'SELECT '.$profil.' FROM joueur INNER JOIN perso ON perso.id_perso=:id AND perso.idJoueur_perso  = joueur.id_joueur';
		
		$request = $db->prepare($query);
		$request->bindParam('id', $id, PDO::PARAM_INT);
		$request->execute();
		$result = (boolean) $request->fetchColumn();
		
		return $result;
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
