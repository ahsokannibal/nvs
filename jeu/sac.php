<?php
session_start();
require_once("../fonctions.php");
require_once("../mvc/model/Item.php");
require_once("../mvc/model/Perso.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){

	// recupération config jeu
	$dispo = config_dispo_jeu($mysqli);
	$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

	if($dispo == '1' || $admin){

//récuperation des variables de sessions
		$id_perso = $_SESSION["id_perso"];

		$perso = new Perso();
		$perso = $perso->getPerso($id_perso,[
			'id_perso',
			'type_perso',
			'clan',
			'or_perso',
			'pv_perso',
			'pvMax_perso',
			'pm_perso',
			'pmMax_perso',
			'pa_perso',
			'paMax_perso',
			'perception_perso',
			'recup_perso',
			'charge_perso',
			'chargeMax_perso',
			'bonusPerception_perso',
			'bonusRecup_perso',
			'bonusPM_perso',
			'bonusPA_perso',
			'bourre_perso'
		]);

		$type_p = $perso->type_perso;
		$taux_alcool = $perso->bourre_perso;
		$camp_perso	= $perso->clan;

				// On verifie que le perso soit toujours vivant
		if ($perso->pv_perso <= 0) {
			echo "<font color=red>Vous avez été capturé...</font>";
		}
		else {

						// on récupère les objets et les armes du perso
			$items = $perso->items($_SESSION["id_perso"],'item');
			$weapons = $perso->items($_SESSION["id_perso"],'weapon');

			$total_items_quantity = 0;
			$total_weapons_quantity = 0;
			$total_weight = 0 ;

			// on regroupe les id des types d'objets possédés par le perso et on calcule le poid total et le nombre d'objets/armes

			foreach($items as $item){
				$items_ids[] = $item['id_objet'];
				$total_items_quantity += $item['quantity'];
				$total_weight += $item['poids_objet']*$item['quantity'];
			}

			foreach($weapons as $weapon){
				$total_weapons_quantity += $weapon['quantity'];
				$total_weight += $weapon['poids_arme']*$weapon['quantity'];
			}

			$nb_dans_sac = $total_items_quantity+$total_weapons_quantity;

						// on souhaite supprimer un ticket
			if(isset($_POST['delete_ticket_hidden']) && !empty($_POST['delete_ticket_hidden'])) {

				$dest_ticket_to_delete = $_POST['delete_ticket_hidden'];

				$verif = preg_match("#^[0-9]*[0-9]$#i","$dest_ticket_to_delete");

				if($verif){
					$item = new Item();
					$result = $item->supprimerTicketTrain($id_perso,$dest_ticket_to_delete);

					if($result){
						$_SESSION["flash"] = ['status'=>'success','icon'=>'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z','msg'=>"Le ticket à destination de la gare n°".$dest_ticket_to_delete." a bien été supprimé de votre inventaire"];
					}else{
						$_SESSION["flash"] = ['status'=>'danger','icon'=>'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z','msg'=>"Une erreur est survenue. Si le problème persiste, veuillez contacter les administrateurs du jeu"];
					}
					header('location:sac.php');
					die();
				}
				else {
					// triche ou pas
					$_SESSION["flash"] = ['status'=>'danger','icon'=>'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z','msg'=>"Ce ticket n'est pas un ticket valide"];
				}
			}


						// on souhaite utiliser un objet
			if(isset($_GET["id_obj"]) && !empty($_GET["id_obj"])){

				// On vérifie que le perso possède au moins 1 PA pour utiliser l'objet
				if($perso->pa_perso >= 1){

					// on récupère les caractéristiques de l'objet s'il existe
					$id_obj = $_GET["id_obj"];
					$item = new Item();
					$item = $item->getItem($id_obj);

					if($item) {
						$nom_obj 			= $item->nom_objet;
						$type_obj 			= $item->type_objet;
						$alcool				= $item->contient_alcool;

						// On vérifie que l'objet soit utilisable
						if($type_obj == "N" OR $type_obj == "E"){

							//On vérifie que le perso possède l'objet
							if(in_array($id_obj,$items_ids)) {

								if ($alcool && $taux_alcool >= 2) {
									// le personnage est trop bourré pour consommer de l'alcool
									$msg = "Vous ne pouvez pas consommer plus d'alcool ce tour ci";
									$status = 'warning';
									$icon = 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
								}else{
									$equip = true;

									if ($type_obj == 'E'){
										$equipment = new Item();
										$canBeEquiped = $equipment->canBeEquiped($perso->type_perso,$id_obj);

										if ($canBeEquiped) {
											if (isset($_GET['desequip']) && $_GET['desequip'] == "ok") {
												$equip = false;
												$item->bonusPerception_objet = -$item->bonusPerception_objet;
												$item->bonusRecup_objet = -$item->bonusRecup_objet;
												$item->bonusPv_objet = -$item->bonusPv_objet;
												$item->bonusPm_objet =  -$item->bonusPm_objet;
												$item->bonusPA_objet =  -$item->bonusPA_objet;

												$msg = 'Vous avez enlevé "'.$nom_obj.'"';
											}
											else {
												$msg = 'Vous avez équipé "'.$nom_obj.'"';
											}
										}else{
											$icon = 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
											$msg = "Vous ne pouvez pas équiper cet objet !";
											$status = 'danger';

											$_SESSION["flash"] = ['status'=>$status,'icon'=>$icon,'msg'=>$msg];

											header("location:sac.php");
											die();
										}
									}else{
										$msg = 'Vous avez utilisé "'.$nom_obj.'"';
									}

									$msg .= ' - Coût : '.$item->coutPa_objet.' PA';


									// on utilise/équipe l'objet et on applique les bonus
									$result = $perso->useItem($perso,$item,$equip);

									if($result){

										// Affichage
										$icon = "M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z";
										$status = 'primary';

										// affichage si l'objet donne des bonus/malus
										if($item->bonusRecup_objet) {
											$recup_perso = $perso->recup_perso+$perso->bonusRecup_perso;
											$msg .= "<br>Votre récupération passe de ".$recup_perso." à ".($recup_perso+$item->bonusRecup_objet);


										}
										if($item->bonusPv_objet) {
											$msg .= "<br>Vos PV passent de ".$perso->pv_perso." à ".($perso->pv_perso+$item->bonusPv_objet)." dans la limite de ".$perso->pvMax_perso." PV";

										}
										if($item->bonusPm_objet) {
											$pm_Maxperso = $perso->pmMax_perso+$perso->bonusPM_perso;
											$msg .= "<br>Vos PM max. passent de ".$pm_Maxperso." à ".($pm_Maxperso+$item->bonusPm_objet)." PM";

										}
										if($item->bonusPA_objet) {
											$paMaxperso = $perso->paMax_perso+$perso->bonusPA_perso;
											$msg .= "<br>Vos PA max. passent à ".$paMaxperso." à ".($paMaxperso+$item->bonusPA_objet)." PA";

										}
										if($item->bonusPerception_objet) {
											$perception_perso = $perso->perception_perso+$perso->bonusPerception_perso;
											if($alcool && $item->bonusPerception_objet<0){
												$msg .= "<br>Vous êtes bourré ! Votre perception en prend un coup : Perception ".$item->bonusPerception_objet;
											}
											$msg .= "<br>Votre perception passe de ".$perception_perso." à ".($perception_perso+$item->bonusPerception_objet);

										}
									}else{
										$icon = 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
										$msg = "Une erreur est survenue. Si le problème persiste, veuillez contacter les administrateurs";
										$status = 'danger';

									}
								}

								$_SESSION["flash"] = ['status'=>$status,'icon'=>$icon,'msg'=>$msg];


								header("location:sac.php");
							die();


							}else {
								$_SESSION["flash"] = ['status'=>'danger','icon'=>'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z','msg'=>"Vous ne possédez pas ou plus cet objet"];
							}
						}else {
							$_SESSION["flash"] = ['status'=>'danger','icon'=>'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z','msg'=>"Impossible de consommer / équiper cet objet !"];
						}
					}else {
						$_SESSION["flash"] = ['status'=>'danger','icon'=>'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z','msg'=>"Cet objet n'existe pas"];
					}
				}else {
					$_SESSION["flash"] = ['status'=>'danger','icon'=>'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z','msg'=>"Vous n'avez pas assez de PA, l'utilisation d'un objet coute 1 PA"];
				}

				header("location:sac.php");
			die();
			}

			if ($camp_perso == 1) {
				$image_sac = "sac_nord.png";
			}
			else if ($camp_perso == 2) {
				$image_sac = "sac_sud.png";
			}
			else {
				$image_sac = "";
			}


			require_once('../mvc/view/item/index.php');

			}
}
	else{
		// logout
		$_SESSION = array(); // On ecrase le tableau de session
		session_destroy(); // On detruit la session

		header("Location:../index.php");
	}

}
else {
	$error = 401;
	http_response_code($error);
	require_once('../mvc/view/errors/'.$error.'.php');

	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session
}

?>
