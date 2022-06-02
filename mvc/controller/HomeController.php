<?php
require_once("mvc/model/User.php");
require_once("mvc/model/Perso.php");
require_once("mvc/model/News.php");

// require_once("mvc/model/news.php");

require_once("Controller.php");

class HomeController extends Controller
{
    /**
     * Display the guest home page.
     *
     * @return view
     */
    public function guest()
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
		
		$user = new User();
		$nb_inscrits = $user->countUsers();
		$dernier_inscrit = $user->lastRegistered();
		
		$perso = new Perso();
		$nord_actifs = $perso->countPersos(1);
		$sud_actifs = $perso->countPersos(2);
		
		$news = new News();
		$lastNews = $news->getNews();
		
		return require_once('mvc/view/guest.php');
    }
	
	/**
     * Display the authenticated home page.
     *
     * @return view
     */
    public function home()
    {
		return require_once('mvc/view/home.php');
    }
}