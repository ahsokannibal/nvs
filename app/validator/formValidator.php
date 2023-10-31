<?php
require_once("../mvc/model/Model.php");

//Form validator
class formValidator
{
	/**
	* Nettoie les données utilisateur
	* @return bool
	*/
	public function sanitize($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		
		return $data;
	}
	
	/**
	* Vérifie que la donnée existe dans une table
	* @return bool
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
	* @return bool
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
				$value = null;
				
				// la condition "bail" permet de stopper à la première erreur rencontrée au lieu de tester toutes les conditions d'un champ
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
				//traitement particuliers pour la condition "in"
				if(strpos($condition,':')){
					$value = substr(strstr($condition,':'),1);
					$condition = strstr($condition,':',true);
					
					if($condition!='in'){
						if(strpos($value,',')){
							$detail = substr(strstr($value,','),1);
							$value = strstr($value,',',true);
						}
					}else{
						if(!is_array($value)){
							$value = explode(',',$value);
						}
					}
					
				}

				//règles de vérification. Il faudra en ajouter selon le besoin. Attention à bien utiliser les "condition:valeur,detail" si nécessaire
				if(isset($_POST[$key])){/* traitement des $_POST */
				
					$type = gettype($_POST[$key]);
				
					// adapte la variable pour les comparaisons de "minimum", "maximum", "plus grand que" ou "plus petit que". le test "numeric" doit être effectué avant si on veut des nombres
					if(!$numeric){
						switch($type){
							case 'string':
								$item_count = strlen($_POST[$key]);
								$item_type = ' caractère(s)';
								break;
							case 'array':
								$item_count = count($_POST[$key]);
								$item_type = '';
								break;
							default :
								$item_count = $_POST[$key];
								$item_type = '';
						}
					}else{
						$item_count = (int) $_POST[$key];
						$item_type = '';
					}
				
					switch($condition){
						case 'required':
							if($_POST[$key]==='' || $_POST[$key]===null){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" est obligatoire';
							}else{
								$bail = 0;
							}
							break;
						case 'not_required':
							if($_POST[$key]==='' || $_POST[$key]===null){
								break;
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
						case 'string':
							if(!is_string($_POST[$key])){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être une chaîne de caractères';
							}else{
								$bail = 0;
							}
							break;
						case 'boolean':
							if(!is_bool($_POST[$key])){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" ne correspond pas aux valeurs attendues';
							}else{
								$bail = 0;
							}
							break;
						case 'checked':
							$values = ['yes','on',1,true];
							if(!in_array($_POST[$key],$values,true)){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" ne correspond pas aux valeurs attendues';
							}else{
								$bail = 0;
							}
							break;
						case 'greater':
							if($item_count<=$value){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être supérieur à '.$value.$item_type.'';
							}else{
								$bail = 0;
							}
							break;
						case 'less':
							if($item_count>=$value){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être inférieur à '.$value.$item_type.'';
							}else{
								$bail = 0;
							}
							break;
						case 'min':
							if($item_count<$value){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être supérieur ou égal à '.$value.$item_type.'';
							}else{
								$bail = 0;
							}
							break;
						case 'max':
							if($item_count>$value){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" doit être inférieur ou égal à '.$value.$item_type.'';
							}else{
								$bail = 0;
							}
							break;
						case 'in':
							if(!in_array($_POST[$key],$value)){
								$errors[$key][$condition] = 'le champ "'.$key_name.'" ne correspond à une valeur attendue';
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
								$errors[$key][$condition] = "l'élément existe déjà dans la table ".$value."";
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
						case 'not_required':
							if($_FILES[$key]['name']==='' || $_FILES[$key]['name']===null){
								break;
							}else{
								$bail = 0;
							}
							break;
						case 'image':
							if(empty($value)){
								$value = ['image/jpeg','image/png','image/gif'];
								$item_type = 'image (png, gif ou jpeg)';
							}else{
								$item_type = $value;
								$value = ['image/'.$value];
							}
							if(!empty($_FILES[$key]['type']) && !in_array($_FILES[$key]['type'],$value)){
								$errors[$key][$condition] = 'le champ doit être un fichier '.$item_type;
							}else{
								$img = getimagesize($_FILES[$key]['tmp_name']);
								$bail = 0;
							}
							break;
						case 'max':
							if($_FILES[$key]['size']>$value || $_FILES[$key]['error']==2){
								$errors[$key][$condition] = 'le fichier est trop volumineux';
							}else{
								$bail = 0;
							}
							break;
						case 'width':
							$img = getimagesize($_FILES[$key]['tmp_name']);
							if($img[0]>(int)$value){
								$errors[$key][$condition] = "la largeur de l'image ne doit pas dépasser ".$value." px";
							}else{
								$bail = 0;
							}
							break;
						case 'height':
							$img = getimagesize($_FILES[$key]['tmp_name']);
							if($img[1]>(int)$value){
								$errors[$key][$condition] = "la hauteur de l'image ne doit pas dépasser ".$value." px";
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