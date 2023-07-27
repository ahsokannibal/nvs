<?php
require_once("../mvc/model/Map.php");
require_once("../mvc/model/Administration.php");
require_once("controller.php");
require_once("../app/validator/formValidator.php");

class MapController extends Controller
{
    /**
     * Display the map index.
     *
     * @return view
     */
    public function index()
    {
		$admin = new Administration();
		$dispo = $admin->getMaintenanceMode();
		
		$map = new Map();
		$mapTablesInDatabase = $map->mapTables;
		
		$maps = [];
		
		foreach($mapTablesInDatabase as $key => $value){
			if($map->carteExist($value)){
				$maps[] = $value;
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
		$errors = isset($_SESSION['errors'])?$_SESSION['errors']:'';
		$old_input = isset($_SESSION['old_input'])?$_SESSION['old_input']:'';
		
		$map = new Map();
		$mapTablesInDatabase = $map->mapTables;
		$grounds = $map->grounds;

		$emptyMaps = 	[];	
		
		foreach($mapTablesInDatabase as $key => $value){
			if(!$map->carteExist($value)){
				$emptyMaps[] = $value;
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
		$map = new Map();
		$map = $map->getMap($id_carte);
		$dimensions = $map->dimensions($id_carte);
        
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
			
			if($_POST['create_map']=='virgin'){
				$errors = formValidator::validate(
					[
						'virgin_choix_carte' => [['bail','required','numeric','greater:0'],'choix de la carte'],
						'virgin_creation_x_max' => [['bail','required','numeric','greater:0'],'X max'],
						'virgin_creation_y_max' => [['bail','required','numeric','greater:0'],'Y max'],
						'virgin_terrain' => ['numeric','greater:0'],
					]
				);
			}elseif($_POST['create_map']=='fromImg'){
				$errors = formValidator::validate(
					[
						'fromImg_choix_carte' => [['bail','required','numeric','greater:0'],'Choix de la carte'],
						'fromImg_img' => [['bail','required','image/png','max:2000000','width:201','height:201'],'image'],// pour les images, d'abord déclarer si on est sur une image PNG et ensuite indiquer les tailles. Pas l'inverse
					]
				);
			}

			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de création
			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;

				header('location:?action=create');
				die();
			}else{
				// sinon on crée la carte
				$map = new Map();
				$terrains = $map->grounds;
				
				if($_POST['create_map']=='virgin'){
					if(isset($_POST['virgin_terrain']) && !empty($_POST['virgin_terrain'])){
						$result = $map->createFromScratch($_POST['virgin_choix_carte'],$_POST['virgin_creation_x_max'],$_POST['virgin_creation_y_max'],$_POST['virgin_terrain']);
						$terrain = $terrains[$_POST['virgin_terrain']][0];
					}else{
						$result = $map->createFromScratch($_POST['virgin_choix_carte'],$_POST['virgin_creation_x_max'],$_POST['virgin_creation_y_max']);
						$terrain = 'plaine';
					}
					$num_carte = $_POST['virgin_choix_carte'];
					$width = $_POST['virgin_creation_x_max'];
					$height = $_POST['virgin_creation_y_max'];
				}
				elseif($_POST['create_map']=='fromImg'){
					
					$dimensions = getimagesize($_FILES['fromImg_img']['tmp_name']);
					$result = $map->createFromPng($_POST['fromImg_choix_carte'],$_FILES['fromImg_img']);
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
				$_SESSION['flash'] = $statut;
				header('location:?action=create');
				die();
			}
		}else{
			header('location:?action=create');
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
		$map = new Map();
		$terrains = $map->grounds;
		
		// si la carte n'existe pas
		
		$map_exist = $map->carteExist($id);

		if(!$map_exist){
			// on retourne la vue et une notification
			$_SESSION['flash'] = ['class'=>'info','message'=>"La carte n°$id n'existe pas"];
			$map = null;
			header('location:?');
			die();
		}
		
		$dimensions = $map->dimensions($id);
		
		if($_SERVER['REQUEST_METHOD']==='POST'){
			// Validation du formulaire
			
			$errors =[];
			
			if($_POST['form']=='showArea'){
				$errors = formValidator::validate(
					[
						'x_pos' => [['bail','required','numeric','min:0'],'position X'],
						'y_pos' => [['bail','required','numeric','min:0'],'position Y'],
						'perc' => [['bail','required','numeric','greater:0'],'perception'],
					]
				);
			}

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				header('location:?action=edit&id='.$id.'');
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
					
					$map = $map->getCarteWithPerc($id,$x_choice,$y_choice,$perc);

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
		
		return require_once('../mvc/view/map/edit.php');
    }
	
	/**
     * Store the updated map in the database
     *
     * @return redirect response
     */
    public function update($id)
	{
		if($_SERVER['REQUEST_METHOD']==='POST'){
			
			$map = new Map();
			$terrains = $map->grounds;
			
			// si la carte n'existe pas
			if(!$map->carteExist($id)){
				// on retourne la vue et un statut particulier
				$statut = ['class'=>'info','message'=>"La carte n°$id n'existe pas"];
				$map = null;
				return require_once('../mvc/view/map/edit.php');
				die();
			}
			
			$dimensions = $map->dimensions($id);
		
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
				header('location:?action=edit&id='.$id.'');
				die();
			}else{
				if($_POST['form']=='editMap'){
					$result = $map->createGroundArea($id,$_POST['x_min'],$_POST['x_max'],$_POST['y_min'],$_POST['y_max'],$_POST['terrain']);
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
				header("location:?action=edit&id=$id");
				die();
			}
		}else{
			header("location:?action=edit&id=$id");
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
		$admin = new Administration();
		$dispo = $admin->getMaintenanceMode();
		
		if($id==1 && $dispo['valeur_config']==1){
			$_SESSION['flash'] = ["class"=>"danger","message"=>"Attention ! La carte n°$id ne peut pas être supprimée si le jeu n'est pas en maintenance"];
		}else{
			$map = new Map();
			$result = $map->destroy($id);
			
			if($result){
				$_SESSION['flash'] = ["class"=>"success","message"=>"La carte n°$id a été supprimée"];
			}else{
				$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
			}
		}
		
		header('location:?');
		die();
    }
}