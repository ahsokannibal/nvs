<?php
require_once("Model.php");

class User extends Model
{
	protected $id;
	protected $name;
	protected $desc;
	protected $email;
	protected $age;
	protected $country;
	protected $password;
	protected $created_at;
	protected $updated_at;
	
	/**
     * Récupère les infos d'un utilisateur
     *
     * @return class User
     */
	public function getUser($id,$attributs = []){
		$db = $this->dbConnectPDO();
		
		if($attributs){
			$attributs = implode(', ',$attributs);
		}else{
			$attributs = "*";
		}
		
		$query = 'SELECT '.$attributs.' FROM USERS WHERE id=:id';
		
		$request = $db->prepare($query);
		$request->bindParam('id', $id, PDO::PARAM_INT);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_CLASS,get_class($this));
		$result = $request->fetch();

		return $result;
	}
	
	/**
     * récupérer le nombre de joueurs inscrits
     *
     * @return int 
     */
	public function countUsers(){
		
		$db = $this->dbConnectPDO();
		
		$query = 'SELECT COUNT(*) FROM USERS';
		
		$request = $db->prepare($query);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_NUM);
		$result = $request->fetch();

		return $result[0];
	}
	
	/**
     * récupérer le nom du personnage principal du dernier utilisateur inscrit
     *
     * @return string
     */
	public function lastRegistered(){
		
		$db = $this->dbConnectPDO();
		
		$query = 'SELECT nom_perso as perso, CAMPS.NAME as camp, CAMPS.DESC, CAMPS.COLOR FROM PERSO INNER JOIN USERS ON USERS.ID = PERSO.IDJOUEUR_PERSO INNER JOIN CAMPS ON CAMPS.ID = PERSO.CLAN ORDER BY USERS.CREATED_AT DESC LIMIT 1';
		
		$request = $db->prepare($query);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_OBJ);
		$result = $request->fetch();

		return $result;
	}
	
	
	
	/**
     * récupérer les rôles d'un joueur
     *
     * @return roles 
     */
	public function roles($id){
		$db = $this->dbConnectPDO();
		
		$query = 'SELECT * FROM ROLES INNER JOIN ROLE_USER ON ROLES.ID = ROLE_USER.ROLE_ID WHERE ROLE_USER.USER_ID = :id';
		
		$request = $db->prepare($query);
		$request->bindParam('id', $id, PDO::PARAM_INT);
		$request->execute();
		$result = (boolean) $request->fetchColumn();
		
		return $result;
	}
	
    /**
     * Savoir si un joueur à le rôle indiqué
     *
     * @return bool
     */
	public function is($id, $role){
		$db = $this->dbConnectPDO();

		$query = 'SELECT COUNT(*) FROM ROLE_USER INNER JOIN ROLES ON ROLES.ID = ROLE_USER.ROLE_ID WHERE ROLES.SLUG = :role AND ROLE_USER.USER_ID = :id';
		
		$request = $db->prepare($query);
		$request->bindParam('role', $role, PDO::PARAM_INT);
		$request->bindParam('id', $id, PDO::PARAM_INT);
		$request->execute();
		$result = (boolean) $request->fetchColumn();
		
		return $result;
	}
}