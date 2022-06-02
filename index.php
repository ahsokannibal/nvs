<?php
//Routeur avec gestion d'erreurs

/*
* si l'utilisateur est authentifié accéder au jeu sinon renvoyer vers les pages sans authentification (accueil, inscription, règles etc.)
* Accès au jeu si le mode "maintenance" est sur OFF ou si le joueur est admin.
* Le lancement de la session se fait ici. Cela évite de le gérer ailleurs sur le site et de créer des conflits ou des blocages.
* la page de configuration est appelée ici ainsi que les modèles permettant l'initialisation de session ou le contrôle d'authentification (principalement USER).
* Tous les contrôleurs utilisés sur l'appli doivent être appelés en début de script.
*/ 

session_start();

require_once('app/config.php');
require_once('mvc/model/User.php');

require_once("mvc/controller/HomeController.php");
require_once("mvc/controller/AuthController.php");
// require_once("mvc/controller/MapController.php");
// require_once("mvc/controller/AdminController.php");

	
$request_method = strtoupper($_SERVER['REQUEST_METHOD']);

try {
	if ($request_method === 'GET') {
		// génération d'un token contre le CSRF
		$_SESSION['_token'] = bin2hex(random_bytes(35));
	}

	if(!isset($_SESSION['Auth']) OR empty($_SESSION['Auth'])){
		$_SESSION['Auth'] = [
			'authenticated' => false,
			'user'=>'guest',
			'id'=>null
		];
	}
	
	$user = new User();

	if(MAINTENANCE_MODE AND !$user->is($_SESSION['Auth']['id'],'admin')){
		if(!isset($_GET['p']) OR !($_GET['p']=='login' OR $_GET['p']=='admin')){
			throw new Exception('Service Unavailable',5031);
		}
	}
	
	if(isset($_GET['p']) && !empty($_GET['p'])){
		switch($_GET['p']){
			/* inscrire ici les différentes pages du site avec leur contrôleur
			* Si l'accès doit être authentifié, ne pas oublier le if(!$_SESSION['Auth']['authenticated']) avec l'exception 401
			*/
			case 'login':
			case 'logout':
			case 'register':
			case 'forgot-password':
				$controller = new AuthController;
				break;
			case 'admin':
				if(!$_SESSION['Auth']['authenticated']){throw new Exception('Unauthorized',401);}
				// $controller = new AdminController;
				break;
			case 'map':
				if(!$_SESSION['Auth']['authenticated']){throw new Exception('Unauthorized',401);}
				// $controller = new MapController;
				break;
			default:
				throw new Exception('Not Found',404);
		}
		
		$methods = get_class_methods($controller);
		
		//vérification des méthodes du contrôleur et des $_POST puis redirection
		if($controller != NULL){
			if(isset($_GET['action']) && !empty($_GET['action'])){
				if(!empty($methods) && in_array($_GET['action'],$methods)){
					$action = $_GET['action'];
					
					if(isset($_GET['id']) && !empty($_GET['id'])){
						if(is_numeric($_GET['id'])){
							$id = $_GET['id'];
							
							if(isset($_POST['_method']) && $_POST['_method']=='PUT/PATCH'){
								$controller->update($id);
							}else{
							$controller->$action($id);
							}
						}else{
							throw new Exception('Not Found',404);
						}
					}else{
						$controller->$action();
					}
				}else{
					throw new Exception('Not Found',404);
				}
			}else{
				if(!empty($methods) && in_array($_GET['p'],$methods)){
					$action = $_GET['p'];
					
					if(!empty($_POST)){
						if(isset($_POST['_method'])){
							if(isset($_GET['id']) && !empty($_GET['id'])){
								if(is_numeric($_GET['id'])){
									switch($_POST['_method']){
										case 'PUT/PATCH':
											echo 'update data';
											// $controller->update($id);
											die();
											break;
										case 'DELETE':
											echo 'destroy data';
											// $controller->update($id);
											die();
											break;
									}
								}else{
									throw new Exception('Not Found',404);
									 // exception à personnaliser ou logger éventuellement
									 // ex : erreur : id non numérique
								}
							}else{
								throw new Exception('Not Found',404);
								 // exception à personnaliser ou logger éventuellement
								 // ex : erreur : id n'est pas un id valide
							}
						}else{
							$controller->$action();
						}
					}else{
						if(get_class($controller) === 'AuthController'){
							$controller->$action();
						}else{
							if(isset($_GET['id']) && !empty($_GET['id'])){
								if(is_numeric($_GET['id'])){
									$controller->show($_GET['id']);
								}else{
									throw new Exception('Not Found',404);
									 // exception à personnaliser ou logger éventuellement
									 // ex : erreur : id non numérique
								}
							}else{
								$controller->index();
							}
						}
					}
				}else{
					throw new Exception('Not Found',404);
				}
			}
		}else{
			throw new Exception('Not Found',404);
		}
	}
	else {
		if(!empty($_GET)){
			throw new Exception('Not Found',404);
		}
		$controller = new HomeController;
		if($_SESSION['Auth']['authenticated']){//authentifié
			$controller->home();
		}else{
			$controller->guest();
		}
	}
}
catch(Exception $e) { // Gestion des erreurs avec renvoi vers une page détaillée selon les situations
	switch($e->getCode()){
		case 401:
			http_response_code(401);
			$controller = new AuthController;
			$controller->login();
			break;
		case 403:
			http_response_code(403);
			echo "vous n'êtes pas autorisé à accéder à cette page";
			// require_once('mvc/view/errors/403.php');
			break;
		case 404:
			http_response_code(404);
			require_once('mvc/view/errors/404.php');
			break;
		case 405:
			http_response_code(405);
			require_once('mvc/view/errors/405.php');
			break;
		case 5031:
			http_response_code(503);
			require_once('mvc/view/maintenance.php');
			break;
		default:
			http_response_code(404);
			require_once('mvc/view/errors/404.php');
			break;
	}
}