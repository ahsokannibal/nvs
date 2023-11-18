<?php
require_once("../mvc/model/Item.php");
require_once("../mvc/model/Administration.php");
require_once("controller.php");
require_once("../app/validator/formValidator.php");

class ItemController extends Controller
{
    /**
     * Display the item index.
     *
     * @return view
     */
    public function index()
    {	
		$items = new Item();
		$items = $items->get();
		
		return require_once('../mvc/view/item/admin/index.php');
    }
	
	/**
     * Display the item creation page.
     *
     * @return view
     */
    public function create()
    {	
		return require_once('../mvc/view/item/admin/create.php');
    }
	
	/**
     * Display the specified item.
     *
     * @param  $id item id
     * @return view
     */
    public function show($id)
    {
    }
	
	/**
     * Store the item in the database
     *
     * @return view or redirect
     */
    public function store()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){
			
			// Validation du formulaire
			
			$errors =[];
			
			$errors = formValidator::validate([
				// général
				'name' => [['bail','required','string','max:50'],'nom'],
				'category' => [['bail','required','in:N,E,RP'],'catégorie'],
				'desc' => [['bail','required','string','max:250'],'description'],
				// caractéristiques
				'weight' => [['bail','required','numeric','min:0'],'poids'],
				'price' => [['bail','required','numeric','min:0'],'prix'],
				'cost_PA' => [['bail','required','numeric','min:0'],'PA'],
				'range' => [['bail','required','numeric','min:0'],'portée'],
				'loss_chance' => [['bail','required','numeric','min:0','max:100'],'probabilité de perte'],
				//options
				'alcohol' => [['bail','checked'],'alcool'],
				'exchangeable' => [['bail','checked'],'échangeable'],
				'droppable' => [['bail','checked'],'déposable'],
				'buyable' => [['bail','checked'],'achetable'],
				//bonus/malus
				'perc' => [['bail','not_required','numeric'],'perception'],
				'recup' => [['bail','not_required','numeric'],'récupération'],
				'pv' => [['bail','not_required','numeric'],'PV'],
				'movement' => [['bail','not_required','numeric'],'PM'],
				'pa' => [['bail','not_required','numeric'],'PA'],
				'defense' => [['bail','not_required','numeric'],'protection'],
				'prec_cac' => [['bail','not_required','numeric'],'précision au CaC'],
				'prec_dist' => [['bail','not_required','numeric'],'Précision à distance'],
				//image
				'imgUpload' => [['bail','required','image','max:2000000','width:150','height:150'],'icône']
			]);

			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de création

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] = ['class'=>'danger','message'=>"le formulaire comporte une ou plusieurs erreurs"];
				
				header('location:?action=create');
				die();
			}else{
				
				// on nettoie les données et on les injecte dans le modèle
				$sanitizer = new formValidator();
				
				$item = new Item();
				$item->nom_objet = $sanitizer->sanitize($_POST['name']);
				$item->description_objet = $sanitizer->sanitize($_POST['desc']);
				$item->type_objet = $sanitizer->sanitize($_POST['category']);

				$item->coutPa_objet = intval($_POST['cost_PA']);
				$item->poids_objet = floatval($_POST['weight']);
				$item->coutOr_objet = intval($_POST['price']);
				$item->portee_objet = intval($_POST['range']);
				$item->Perte_Proba = intval($_POST['loss_chance']);
				
				$item->contient_alcool = intval(boolval($_POST['alcohol'] ?? ''));
				$item->echangeable = intval(boolval($_POST['exchangeable'] ?? ''));
				$item->deposable = intval(boolval($_POST['droppable'] ?? ''));
				$item->achetable = intval(boolval($_POST['buyable'] ?? ''));				
				
				$item->bonusPerception_objet = intval($_POST['perc']);
				$item->bonusRecup_objet = intval($_POST['recup']);
				$item->bonusPv_objet = intval($_POST['pv']);
				$item->bonusPm_objet = intval($_POST['movement']);
				$item->bonusDefense_objet = intval($_POST['defense']);
				$item->bonusPrecisionCac_objet = intval($_POST['prec_cac']);
				$item->bonusPrecisionDist_objet = intval($_POST['prec_dist']);
				$item->bonusPA_objet = intval($_POST['pa']);
				
				//traitement de l'image
				$fileName = basename($_FILES['imgUpload']['name']);
				$extension = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
				
				$imgPath = $_SERVER['DOCUMENT_ROOT'].'/public/img/items';

				$imgName = $sanitizer->sanitize(str_replace(' ','_',strtolower($_POST['name'])));
				$imgName = $imgName.uniqid();
				$item->image_objet = $imgName.'.'.$extension;
				
				$destinationPath = $imgPath.'/'.$imgName.'.'.$extension;
				
				//enregistrement de l'image sur le serveur
				$imgUploaded = move_uploaded_file($_FILES['imgUpload']['tmp_name'],$destinationPath);
				
				if($imgUploaded){
					$result = $item->save();
				}

				if($result){
					$_SESSION['flash'] = ['class'=>'success','message'=>"L'objet \"".$_POST['name']."\" a bien été créé"];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				
				header('location:?action=create');
			}
		}else{
			header('location:?action=create');
			die();
		}
    }
	
	/**
     * Display the item edit page.
     *
     * @param $id item id
     * @return view
     */
    public function edit(int $id)
    {	
		// if($_SESSION['old_input']){
		// var_dump('<pre>',$_SESSION['old_input'],'</pre>');
		// die();
		// }
		$item = new Item();
		$item = $item->find($id);
		
		return require_once('../mvc/view/item/admin/edit.php');
    }
	
	/**
     * Store the updated item in the database
     *
	 * @param $id item id
     * @return redirect response
     */
    public function update(int $id)
	{
		if($_SERVER['REQUEST_METHOD']==='POST'){

			// Validation du formulaire
			
			$errors =[];
			
			$errors = formValidator::validate([
				// général
				'name' => [['bail','required','string','max:50'],'nom'],
				'category' => [['bail','required','in:N,E,RP'],'catégorie'],
				'desc' => [['bail','required','string','max:250'],'description'],
				// caractéristiques
				'weight' => [['bail','required','numeric','min:0'],'poids'],
				'price' => [['bail','required','numeric','min:0'],'prix'],
				'cost_PA' => [['bail','required','numeric','min:0'],'PA'],
				'range' => [['bail','required','numeric','min:0'],'portée'],
				'loss_chance' => [['bail','required','numeric','min:0','max:100'],'probabilité de perte'],
				//options
				'alcohol' => [['bail','checked'],'alcool'],
				'exchangeable' => [['bail','checked'],'échangeable'],
				'droppable' => [['bail','checked'],'déposable'],
				'buyable' => [['bail','checked'],'achetable'],
				//bonus/malus
				'perc' => [['bail','not_required','numeric'],'perception'],
				'recup' => [['bail','not_required','numeric'],'récupération'],
				'pv' => [['bail','not_required','numeric'],'PV'],
				'movement' => [['bail','not_required','numeric'],'PM'],
				'pa' => [['bail','not_required','numeric'],'PA'],
				'defense' => [['bail','not_required','numeric'],'protection'],
				'prec_cac' => [['bail','not_required','numeric'],'précision au CaC'],
				'prec_dist' => [['bail','not_required','numeric'],'Précision à distance'],
				//image
				'imgUpload' => [['bail','not_required','image','max:2000000','width:150','height:150'],'icône']
			]);

			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de création

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] = ['class'=>'danger','message'=>"le formulaire comporte une ou plusieurs erreurs"];
				
				header('location:?action=edit&id='.$id);
				die();
			}else{
				
				// on nettoie les données et on les injecte dans le modèle
				$sanitizer = new formValidator();
				
				$item = new Item();
				$item = $item->find($id);
				
				$item->nom_objet = $sanitizer->sanitize($_POST['name']);
				$item->description_objet = $sanitizer->sanitize($_POST['desc']);
				$item->type_objet = $sanitizer->sanitize($_POST['category']);

				$item->coutPa_objet = intval($_POST['cost_PA']);
				$item->poids_objet = floatval($_POST['weight']);
				$item->coutOr_objet = intval($_POST['price']);
				$item->portee_objet = intval($_POST['range']);
				$item->Perte_Proba = intval($_POST['loss_chance']);
				
				$item->contient_alcool = intval(boolval($_POST['alcohol'] ?? ''));
				$item->echangeable = intval(boolval($_POST['exchangeable'] ?? ''));
				$item->deposable = intval(boolval($_POST['droppable'] ?? ''));
				$item->achetable = intval(boolval($_POST['buyable'] ?? ''));				
				
				$item->bonusPerception_objet = intval($_POST['perc']);
				$item->bonusRecup_objet = intval($_POST['recup']);
				$item->bonusPv_objet = intval($_POST['pv']);
				$item->bonusPm_objet = intval($_POST['movement']);
				$item->bonusDefense_objet = intval($_POST['defense']);
				$item->bonusPrecisionCac_objet = intval($_POST['prec_cac']);
				$item->bonusPrecisionDist_objet = intval($_POST['prec_dist']);
				$item->bonusPA_objet = intval($_POST['pa']);
				
				//traitement de l'image
				if(!empty($_FILES['imgUpload']['tmp_name'])){

					$fileName = basename($_FILES['imgUpload']['name']);
					$extension = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
					
					$imgPath = $_SERVER['DOCUMENT_ROOT'].'/public/img/items';

					$imgName = $sanitizer->sanitize(str_replace(' ','_',strtolower($_POST['name'])));
					$imgName = $imgName.uniqid();
					$old_image = $item->image_objet;
					$item->image_objet = $imgName.'.'.$extension;
					
					if($old_image!=$item->image_objet){
						$unlinkPath = $imgPath.'/'.$old_image;
						unlink($unlinkPath);
					}
					
					$destinationPath = $imgPath.'/'.$imgName.'.'.$extension;
					
					//enregistrement de l'image sur le serveur
					$imgUploaded = move_uploaded_file($_FILES['imgUpload']['tmp_name'],$destinationPath);
					
					if($imgUploaded){
						$result = $item->update($id);
					}
				}else{
					$result = $item->update($id);
				}
				
				if($result){
					$_SESSION['flash'] = ['class'=>'success','message'=>"L'objet \"".$_POST['name']."\" a bien été edité"];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				
				header('location:?action=edit&id='.$id);
			}
			
		}else{
			header("location:?action=edit&id=$id");
			die();
		}
    }
	
	/**
     * delete the item from database
     *
	 * @param $id item id
     * @return redirect
     */
    public function destroy($id)
    {		
		header('location:?');
		die();
    }
}