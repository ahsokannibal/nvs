<?php
require_once("../mvc/model/Model.php");

//Form validator
class formValidator
{
	/**
	* Vérifie que la donnée existe dans une table
	* @return array of errors
	*/
	private function exists($key,$value,$table,$column=null) {
		
		if($column===null){
			$column = $key;
		}
		$db = $this->dbConnectPDO();
		$query = "SELECT * FROM $table WHERE $column='$value'";
	
		$request = $db->prepare($query);
		$request->execute();
		$result = $request->fetch(PDO::FETCH_ASSOC);
		
		if($result){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	* Vérifie que la donnée est unique dans une table
	* @return array of errors
	*/
	private function unique($key,$value,$table,$column=null) {
		
		if($column===null){
			$column = $key;
		}
		$db = $this->dbConnectPDO();
		$query = "SELECT COUNT(*) FROM $table WHERE $column='$value'";
	
		$request = $db->prepare($query);
		$request->execute();
		$result = $request->fetch(PDO::FETCH_ASSOC);
		
		if($result<2){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	* Vérifie que les données $_POST et $_FILES respectent certaines conditions
	* @return array of errors
	*/
	public static function validate(array $expected_inputs)
    {
		// condition qui permet d'éviter les injections "man in the middle"
		// if(isset($_POST['_token']) AND $_POST['_token'] !== $_SESSION['_token']){
			// throw new Exception('Method Not Allowed',405);
		// }
		
		$errors = [];
		foreach($expected_inputs as $key => $conditions){
			$current = null;
			$numeric = false;
			$key_name = isset($conditions[1]) && !empty($conditions[1])?$conditions[1]:$key;
			
			$conditions = $conditions[0];

			foreach((array)$conditions as $condition){
				
				// la condition "bail" permet de stopper à la première erreur rencontrée après sur les conditions qui suivent au lieu de tester toutes les conditions
				if($condition == 'bail'){
					$bail = 0;
					$current = $key;
				continue;
				}
				if($current==$key){
					$bail++;
				}else{
					$bail = null;
				}
				if($bail > 1){
					continue;
				}
				
				
				//sépare la condition et sa valeur (ex : "greater:0" => condition "supérieur à", valeur "0")
				if(strpos($condition,':')){
					$value = substr(strstr($condition,':'),1);
					$condition = strstr($condition,':',true);
					
					if(strpos($value,',')){
						$detail = substr(strstr($detail,','),1);
						$value = strstr($value,',',true);
					}
				}

				//règles de vérification. Il faudra en ajouter selon le besoin. Attention à bien utiliser les "condition:valeur,detail" si nécessaire
				if(isset($_POST[$key])){/* traitement des $_POST */
				
					$type = gettype($_POST[$key]);
				
					// adapte la variable pour les comparaisons de "minimum", "maximum", "plus grand que" ou "plus petit que". le test "numeric" doit être effectué avant si on veut des nombres
					if(!$numeric){
						switch($type){
							case 'string':
								$item = strlen($_POST[$key]);
								break;
							case 'array':
								$item = count($_POST[$key]);
								break;
							default :
								$item = $_POST[$key];
						}
					}else{
						$item = (int) $_POST[$key];
					}
				
					switch($condition){
						case 'required':
							if($_POST[$key]==='' || $_POST[$key]===null){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" est obligatoire';
							}else{
								$bail = 0;
							}
							break;
						case 'numeric':
							if(!is_numeric($_POST[$key])){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être numérique';
							}else{
								$bail = 0;
								$numeric = true;
							}
							break;
						case 'greater':
							if($item<=$value){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être supérieur à '.$value.'';
							}else{
								$bail = 0;
							}
							break;
						case 'less':
							if($item>=$value){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être inférieur à '.$value.'';
							}else{
								$bail = 0;
							}
							break;
						case 'min':
							if($item<$value){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être supérieur ou égal à '.$value.'';
							}else{
								$bail = 0;
							}
							break;
						case 'max':
							if($item>$value){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être inférieur ou égal à '.$value.'';
							}else{
								$bail = 0;
							}
							break;
						case 'exists':
							if(!$this->exists($key,$_POST[$key],$value,$detail)){
								$errors[$key][$condition] = "l'élément doit exister dans la table ".$value."";
							}else{
								$bail = 0;
							}
							break;
						case 'unique':
							if(!$this->unique($key,$_POST[$key],$value,$detail)){
								$errors[$key][$condition] = "l'élément doit exister dans la table ".$value."";
							}else{
								$bail = 0;
							}
							break;
						case 'accepted':
							if(!in_array($_POST[$key],['yes','on',1,true])){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être accepté';
							}else{
								$bail = 0;
							}
							break;
						case 'same':
							if($_POST[$key]!==$value){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" est incorrect';
							}else{
								$bail = 0;
							}
							break;
					}
				}elseif(isset($_FILES[$key])){/* traitement des fichiers */
					switch($condition){
						case 'required':
							if(empty($_FILES[$key]['name'])){
								$errors[$key][$condition] = 'le champ est obligatoire';
							}else{
								$bail = 0;
							}
							break;
						case 'image/png':
							if(!empty($_FILES[$key]['type']) && $_FILES[$key]['type']!='image/png'){
								$errors[$key][$condition] = 'le champ doit être un fichier PNG valide';
							}else{
								$img = getimagesize($_FILES[$key]['tmp_name']);
								$bail = 0;
							}
							break;
						case 'max':
							if($_FILES[$key]['size']>$value || $_FILES[$key]['error']==2){
								$errors[$key][$condition] = 'le fichier ne doit pas dépasser les 2Mo';
							}else{
								$bail = 0;
							}
							break;
						case 'width':
							$img = getimagesize($_FILES[$key]['tmp_name']);
							if($img[0]>(int)$value){
								$errors[$key][$condition] = "la largeur de l'image ne doit pas dépasser ".$value."px";
							}else{
								$bail = 0;
							}
							break;
						case 'height':
							$img = getimagesize($_FILES[$key]['tmp_name']);
							if($img[1]>(int)$value){
								$errors[$key][$condition] = "la hauteur de l'image ne doit pas dépasser ".$value."px";
							}else{
								$bail = 0;
							}
							break;
					}
				}else{
					switch($condition){
						case 'required':
							$errors[$key][$condition] = 'le champ "'.$key_name.'" est obligatoire';
							break 2;
					}
				}
			}
		}
		return $errors;
    }
}