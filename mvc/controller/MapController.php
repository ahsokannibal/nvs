<?php
require_once("mvc/model/Map.php");

require_once("app/validator/formValidator.php");
require_once("Controller.php");

class MapController extends Controller
{
    /**
     * Display the map index.
     *
     * @return view
     */
    public function index()
    {
		// nettoie les variables de session liées aux formulaires (à refactoriser)
		if(isset($_SESSION['statut'])){
			$statut = $_SESSION['statut'];
			unset($_SESSION['statut']);
		}
		
		$carte = new Carte();
		$carteTablesInDatabase = $carte->carteTables;
		
		$cartes = [];
		
		foreach($carteTablesInDatabase as $key => $value){
			if($carte->carteExist($value)){
				$cartes[] = $value;
			}
		}
		
		return require_once('../mvc/view/map/index.php');
    }
	
	/**
     * Display the map creation page.
     *
     * @return view
     */
    public function create()
    {
		// nettoie les variables de session liées aux formulaires (à refactoriser)
		if(isset($_SESSION['old_input'])){
			$old_input = $_SESSION['old_input'];
			unset($_SESSION['old_input']);
		}
		if(isset($_SESSION['errors'])){
			$errors = $_SESSION['errors'];
			unset($_SESSION['errors']);
		}
		if(isset($_SESSION['statut'])){
			$statut = $_SESSION['statut'];
			unset($_SESSION['statut']);
		}
		
		$carte = new Carte();
		$carteTablesInDatabase = $carte->carteTables;
		$terrains = $carte->terrains;

		$emptyCartes = 	[];	
		
		foreach($carteTablesInDatabase as $key => $value){
			if(!$carte->carteExist($value)){
				$emptyCartes[] = $value;
			}
		}

		return require_once('../mvc/view/map/create.php');
    }
	
	/**
     * Display the specified map.
     *
     * @param  $id de la carte
	 * @param  $id du perso
     * @return view
     */
    public function show($id_carte,$id_perso=null)
    {
		$carte = new Carte();
		$map = $carte->getMap($id_carte);
		$dimensions = $carte->dimensions($id_carte);
        
		return require_once('../mvc/view/map/show.php');
		die();
    }
	
	/**
     * Store the map in the database
     *
     * @return view or redirect
     */
    public function store()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){
			
			// Validation du formulaire
			
			$errors =[];
			
			if($_POST['map']=='virgin'){
				$errors = formValidator::validate(
					[
						'virgin_choix_carte' => ['bail','required','numeric','greater:0'],
						'virgin_creation_x_max' => ['bail','required','numeric','greater:0'],
						'virgin_creation_y_max' => ['bail','required','numeric','greater:0'],
						'virgin_terrain' => ['numeric','greater:0'],
					]
				);
			}elseif($_POST['map']=='fromImg'){
				$errors = formValidator::validate(
					[
						'fromImg_choix_carte' => ['bail','required','numeric','greater:0'],
						'fromImg_img' => ['bail','required','image/png','max:2000000','width:201','height:201'],// pour les images, d'abord déclarer si on est sur une image PNG et ensuite indiquer les tailles. Pas l'inverse
					]
				);
			}

			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de création
			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				header('location:?page=carte&action=create');
				die();
			}else{
				// sinon on crée la carte
				$carte = new Carte();
				$terrains = $carte->terrains;
				
				if($_POST['map']=='virgin'){
					if(isset($_POST['virgin_terrain']) && !empty($_POST['virgin_terrain'])){
						$result = $carte->createFromScratch($_POST['virgin_choix_carte'],$_POST['virgin_creation_x_max'],$_POST['virgin_creation_y_max'],$_POST['virgin_terrain']);
						$terrain = $terrains[$_POST['virgin_terrain']][0];
					}else{
						$result = $carte->createFromScratch($_POST['virgin_choix_carte'],$_POST['virgin_creation_x_max'],$_POST['virgin_creation_y_max']);
						$terrain = 'plaine';
					}
					$num_carte = $_POST['virgin_choix_carte'];
					$width = $_POST['virgin_creation_x_max'];
					$height = $_POST['virgin_creation_y_max'];
				}
				elseif($_POST['map']=='fromImg'){
					$dimensions = getimagesize($_FILES[$key]['tmp_name']);
					$result = $carte->createFromPng($_POST['fromImg_choix_carte'],$_FILES['fromImg_img']);
					$num_carte = $_POST['fromImg_choix_carte'];
					$terrain = 'multiple';
					$width = $dimensions[0];
					$height = $dimensions[0];
				}
				
				if($result){
					$statut = ['class'=>'success','message'=>'La carte n°'.$num_carte.' a été créée : dimensions '.$width.'x'.$height.'<br/> terrain par défaut : '.$terrain.''];
				}else{
					$statut = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				
				// on renvoie le statut dans la session
				$_SESSION['statut'] = $statut;
				header('location:?page=carte&action=create');
				die();
			}
		}else{
			header('location:?page=carte&action=create');
			die();
		}
    }
	
	/**
     * Display the map edit page.
     *
     * @return view
     */
    public function edit($id)
    {
		// nettoie les variables de session liées aux formulaires (à refactoriser)
		if(isset($_SESSION['old_input'])){
			$old_input = $_SESSION['old_input'];
			unset($_SESSION['old_input']);
		}
		if(isset($_SESSION['errors'])){
			$errors = $_SESSION['errors'];
			unset($_SESSION['errors']);
		}
		if(isset($_SESSION['statut'])){
			$statut = $_SESSION['statut'];
			unset($_SESSION['statut']);
		}
		
		$carte = new Carte();
		$terrains = $carte->terrains;
		
		// si la carte n'existe pas
		if(!$carte->carteExist($id)){
			// on retourne la vue et un statut particulier
			$statut = ['class'=>'info','message'=>"La carte n°$id n'existe pas"];
			$carte = null;
			return require_once('../mvc/view/map/edit.php');
			die();
		}
		
		$dimensions = $carte->dimensions($id);
		
		if($_SERVER['REQUEST_METHOD']==='POST'){
			// Validation du formulaire
			
			$errors =[];
			
			if($_POST['form']=='showArea'){
				$errors = formValidator::validate(
					[
						'x_pos' => ['bail','required','numeric','min:0'],
						'y_pos' => ['bail','required','numeric','min:0'],
						'perc' => ['bail','required','numeric','greater:0'],
					]
				);
			}

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				header('location:?page=carte&action=edit&id='.$id.'');
				die();
			}else{
				if($_POST['form']=='showArea'){
					$x_choice = $_POST['x_pos'];
					$y_choice = $_POST['y_pos'];
					$perc = $_POST['perc'];
					
					$x_min = $_POST['x_pos'] - $_POST['perc'];
					$x_max = $_POST['x_pos'] + $_POST['perc'];
					$y_min = $_POST['y_pos'] - $_POST['perc'];
					$y_max = $_POST['y_pos'] + $_POST['perc'];
					
					$map = $carte->getCarteWithPerc($id,$x_choice,$y_choice,$perc);

					if($perc>20){
						$tileClass = 'tile-sm';
					}else{
						$tileClass = '';
					}
					
					return require_once('../mvc/view/map/edit.php');
					die();
				}
			}
		}
		
		return require_once('../mvc/view/carte/edit.php');
    }
	
	/**
     * Store the updated map in the database
     *
     * @return redirect response
     */
    public function update($id)
	{
		if($_SERVER['REQUEST_METHOD']==='POST'){
			
			$carte = new Carte();
			$terrains = $carte->terrains;
			
			// si la carte n'existe pas
			if(!$carte->carteExist($id)){
				// on retourne la vue et un statut particulier
				$statut = ['class'=>'info','message'=>"La carte n°$id n'existe pas"];
				$carte = null;
				return require_once('../mvc/view/map/edit.php');
				die();
			}
			
			$dimensions = $carte->dimensions($id);
		
			$errors =[];
			
			if($_POST['form']=='editMap'){
				// Validation du formulaire
				$errors = formValidator::validate(
					[
						'x_min' => ['bail','required','numeric','min:0'],
						'x_max' => ['bail','required','numeric','greater:0'],
						'y_min' => ['bail','required','numeric','min:0'],
						'y_max' => ['bail','required','numeric','greater:0'],
						'terrain' => ['bail','required','numeric','greater:0'],
					]
				);
			}
			
			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				header('location:?page=carte&action=edit&id='.$id.'');
				die();
			}else{
				if($_POST['form']=='editMap'){
					$result = $carte->createGroundArea($id,$_POST['x_min'],$_POST['x_max'],$_POST['y_min'],$_POST['y_max'],$_POST['terrain']);
					$nbr_cases = ($_POST['x_max']-$_POST['x_min']+1)*($_POST['y_max']-$_POST['y_min']+1);
					$coordo_min = $_POST['x_min'].':'.$_POST['y_min'];
					$coordo_max = $_POST['x_max'].':'.$_POST['y_max'];
					$terrain = $terrains[$_POST['terrain']][0];
				}
				
				if($result){
					$statut = ['class'=>'success','message'=>'La carte n°'.$id.' a été modifiée :<br/>'.$nbr_cases.' cases modifiées dans la zone ('.$coordo_min.') - ('.$coordo_max.')<br/> terrain : '.$terrain.''];
				}else{
					$statut = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				
				// on renvoie le statut dans la session
				$_SESSION['statut'] = $statut;
				header("location:?page=carte&action=edit&id=$id");
				die();
			}
		}else{
			header("location:?page=carte&action=edit&id=$id");
			die();
		}
    }
	
	/**
     * delete the map from database
     *
     * @return ?
     */
    public function destroy($id)
    {
		$carte = new Carte();
		$result = $carte->destroy($id);
		
		if($result){
			$statut = ["class"=>"success","message"=>"La carte n°$id a été supprimée"];
		}else{
			$statut = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
		}
		$_SESSION['statut'] = $statut;
		header('location:?page=carte');
		die();
    }
}