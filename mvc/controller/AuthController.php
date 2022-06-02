<?php
require_once("mvc/model/User.php");
require_once("mvc/model/Perso.php");

require_once("mvc/model/Auth.php");
require_once("app/validator/formValidator.php");
require_once("Controller.php");

class AuthController extends Controller
{
    /**
     * Display the register page.
     *
     * @return view
     */
    public function register()
    {
		if(isset($_SESSION['old_input'])){
			$old_input = $_SESSION['old_input'];
			unset($_SESSION['old_input']);
		}
		if(isset($_SESSION['errors'])){
			$errors = $_SESSION['errors'];
			unset($_SESSION['errors']);
		}
		if(isset($_SESSION['status'])){
			$status = $_SESSION['status'];
			unset($_SESSION['status']);
		}
		
		$perso = new Perso();
		$nord_actifs = $perso->countPersos(1);
		$sud_actifs = $perso->countPersos(2);
		
		return require_once('mvc/view/auth/register.php');
    }
	
	/**
     * Store the created user.
     *
     * @return view
     */
    public function store()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){

			$errors = formValidator::validate(
				[
					'cgu' => ['bail','required','accepted'],
					'charte' => ['bail','required','accepted'],
				]
			);
			
					
			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de création
			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				header('location:?p=register');
				die();
			}else{
				echo 'utilisateur créé';
			}
		}
    }
	
	/**
     * Display the login page
     *
     * @return view
     */
    public function login()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){
			$this->authenticate();
		}
		// return require_once('mvc/view/auth/login.php');
    }
	
	/**
     * Authenticate the login and redirect the answer or an error.
     *
     * @return view
     */
    public function authenticate()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){
			
			$errors = formValidator::validate(
				[
					'pseudo' => ['bail','required'],
					'password' => ['bail','required'],
					'captcha' => ['bail','required','same:'.$_SESSION["captcha"]],
				]
			);
		}
		
		// Si le formulaire contient des erreurs on renvoie :
		// les erreurs, les anciennes données
		// et on redirige vers la page de création
		if (!empty($errors)){
			$_SESSION['old_input'] = $_POST;
			$_SESSION['errors'] = $errors;
			header('location:/');
			die();
		}else{
			$auth = new Auth();
			$check = $auth->checkCredentials([
				$_POST['pseudo'],
				$_POST['password']
				]);

			if($check){
				header('location:/');
				// header('location:?p=home');
			}else{
				$status = ["class" => "danger","message"=>"Mauvais identifiants"];
				
				// on renvoie le status dans la session
				$_SESSION['status'] = $status;
				header('location:/');
				die();
			}
		}
		// return require_once('mvc/view/auth/login.php');
    }
	
	/**
     * Display the login page.
     *
     * @return view
     */
    public function logout()
    {
		$_SESSION = [];
		// session_destroy();
		header('location:/');
		die();
    }
}