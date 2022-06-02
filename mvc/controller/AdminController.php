<?php
require_once("mvc/model/Administration.php");

require_once("Controller.php");

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return view
     */
    public function index()
    {
		$maintenanceMode = new Administration;
		$maintenanceMode = $maintenanceMode->checkMaintenanceMode();
		
		return require_once('../mvc/view/admin/index.php');
    }
	
	/**
     *	Display the Maintenance mode actions
     *
     * @return HTTP redirection
     */
    public function maintenance_mode()
    {
		$admin = new Administration();
		$maintenanceMode = $admin->checkMaintenanceMode();
		
		if($maintenanceMode == 0){
			$value = (boolean) 1;
		}else{
			$value = (boolean) 0;
		}
		
		$result = $admin->switchMaintenance($value);
		
		$_SESSION['maintenance'] = $maintenanceMode;
		
		header('location:../jeu/admin_nvs.php');
    }
}