<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();
$dispo = $t_dispo["disponible"];

if($dispo){

	if (@$_SESSION["id_perso"]) {
		//recuperation des varaibles de sessions
		$pseudo = $_SESSION["nom_perso"];
		$id = $_SESSION["id_perso"];
		$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		$testpv = $tpv['pv_perso'];
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
			$erreur = "<div class=\"erreur\">";
	
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<title>Nord VS Sud</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
	<p align="center"><input type="button" value="Fermer la fenêtre de groupe" onclick="window.close()"></p>
	<?php
	if (isset($_GET["id_section"])){
		$verif = preg_match("#^[0-9]+$#i",$_GET["id_section"]);
		
		if($verif){
			$id_section = $_SESSION["id_section"] = $_GET["id_section"];
			
			// vérification que la section existe
			$sql = "SELECT id_section, id_clan from sections where id_section='$id_section'";
			$res = $mysqli->query($sql);
			$exist = $res->num_rows;
			
			// récupération du camp du groupe
			$sql = "SELECT id_clan from sections where id_section='$id_section'";
			$res = $mysqli->query($sql);
			$t_c = $res->fetch_assoc();
			$clan_section = $t_c["id_clan"];
			
			// récupération du clan du perso
			$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t_cp = $res->fetch_assoc();
			$clan_perso = $t_cp["clan"];
			
			if($exist){
				// vérification que le perso est bien du même camp que le groupe				
				if($clan_perso == $clan_section){
					if (isset($_GET["rejoindre"])) { 
						if($_GET["rejoindre"] == "ok") {// on souhaite rejoindre une section
							$ok_n = 1;
							
							// vérification que le perso est bien du même camp que le groupe				
							if($clan_perso == $clan_section){
							
								// verification que le perso n'est pas déjà dans le groupe
								$sql = "SELECT id_perso FROM perso_in_section WHERE id_section='$id_section'";
								$res = $mysqli->query($sql);
								while ($n = $res->fetch_assoc()){
									$id_n = $n["id_perso"];
									if ($id_n == $id) {
										$ok_n = 0;
										break;
									}
								}
								
								// vérification que le perso n'est pas déjà dans un groupe ou en attente sur une autre
								$sql = "SELECT id_perso FROM perso_in_section WHERE id_perso='$id'";
								$res = $mysqli->query($sql);
								$est_deja = $res->num_rows;
								if($est_deja){
									$ok_n = 0;
								}
								
								// si il peut postuler
								if($ok_n == 1) {
									// mise a jour de la table perso_in_section
									$sql = "INSERT INTO perso_in_section VALUES ('$id','$id_section','5','1')";
									$mysqli->query($sql);
									
									echo "Vous venez de poser votre candidature dans un groupe<br>";
					
									echo "<a href='section.php'> [retour] </a>";
								}
								else {
									echo "<font color = res>Vous êtes déjà inscrit dans un groupe</font>";
								}
							}
							else {
								echo "<center>C'est pas bien d'essayer de postuler dans un groupe adverse...</center>";
							}
						}
						if($_GET["rejoindre"] == "off") { // on souhaite quitter la section
						
							// verification si le perso est le chef
							$sql = "SELECT id_perso, poste_section FROM perso_in_section WHERE id_section=$id_section AND id_perso=$id";
							$res = $mysqli->query($sql);
							$verif = $res->fetch_assoc();
							$chef = $verif["poste_section"];
							if ($chef == 1) { // si c'est le chef de la section
								echo "<center><font color = red>Vous devez d'abords choisir un nouveau chef avant de quitter le groupe</font></center><br>";
								echo "<center><a href='chef_section.php'>changer de chef</a></center>";
								echo "<center><a href='section.php'> [retour] </a></center>";
							}
							else { // on peut le delete
						
								$sql = "DELETE FROM perso_in_section WHERE id_perso=$id";
								$mysqli->query($sql);
								
								// on enleve le perso de la banque
								$sql = "DELETE FROM banque_section WHERE id_perso=$id";
								$mysqli->query($sql);
								
								echo "Vous venez de quitter votre groupe<br>";
							
								// verification du nombre de membres dans la section : si la section n'a plus de membre => delete
								$sql = "SELECT id_perso, poste_section FROM perso_in_section WHERE id_section=$id_section";
								$res = $mysqli->query($sql);
								$nb = $res->fetch_row();
								
								if ($nb == 0){ // il n'y a plus de membres
									$sql = "DELETE FROM sections WHERE id_section=$id_section";
									$mysqli->query($sql);
									echo "Votre depart a detruit le groupe (il n y a plus de membres)<br>";
								}
								echo "<center><a href='section.php'> [retour] </a></center>";
							}
						}
					}
					else { // on souhaite juste avoir des infos sur la section
						// recuperation des information sur la section
						$sql = "SELECT id_section, nom_section, image_section, resume_section, description_section FROM sections WHERE id_section=$id_section";
						$res = $mysqli->query($sql);
						$sec = $res->fetch_assoc();
						$id_section = $sec["id_section"];
						$nom_section = $sec["nom_section"];
						$image_section = $sec["image_section"];
						$resume_section = $sec["resume_section"];
						$description_section = $sec["description_section"];
						
						// affichage des information de la section
						echo "<center><b>$nom_section</b></center>";
						echo "<table border=\"1\" width = 100%><tr><td width=40 height=40><img src=\"".htmlspecialchars($image_section)."\" width=\"40\" height=\"40\"></td><td>".bbcode(htmlentities(stripslashes($resume_section)))."</td><td width=20%><center>Liste des membres</center></td>";
						echo "</tr><tr><td></td><td>".bbcode(htmlentities(stripslashes($description_section)))."</td><td>";
						
						// recuperation de la liste des membres de la section
						$sql = "SELECT nom_perso, poste_section FROM perso, perso_in_section WHERE perso_in_section.id_perso=perso.ID_perso AND id_section=$id_section AND attenteValidation_section='0' ORDER BY poste_section";
						$res = $mysqli->query($sql);
						while ($membre = $res->fetch_assoc()) {
							$poste_section = $membre["poste_section"];
							$nom_membre = $membre["nom_perso"];
							if($poste_section != 5){
								// recuperation du nom de poste
								$sql2 = "SELECT nom_poste FROM poste WHERE id_poste=$poste_section";
								$res2 = $mysqli->query($sql2);
								$t_p = $res2->fetch_assoc();
								$nom_poste = $t_p["nom_poste"];
								
								echo "<center>".$nom_membre." ($nom_poste)</center>";
							}
							else
								echo "<center>".$nom_membre."</center>";
						}
						echo "</td>";
						echo "</tr></table><br>";
						if(isset($_GET['voir_groupe']) && $_GET['voir_groupe'] == 'ok'){
							echo "";
						}
						else {
							echo "<center><a href='section.php?id_section=$id_section&rejoindre=ok'> >>Rejoindre</a></center>";
						}
						echo "<br><center><a href=\"section.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
					}
				}
				else {
					echo "<center>Pas bien d'essayer de consulter les groupes ennemis</center>";
				}
			}
			else {
				echo "<center>Le groupe demandé n'existe pas</center>";
			}
		}
		else {
			echo "<center>Le groupe demandé n'existe pas</center>";
		}
	}
	else {
		// si le perso souhaite voir la liste des sections
		if(isset($_GET['voir_groupe']) && $_GET['voir_groupe']=='ok'){
			echo "<br/><center><b><u>Liste des groupes déjà existants</u></b></center><br/>";
			
			// recuperation des sections existantes
			$sql = "SELECT id_section, nom_section, image_section, resume_section, description_section FROM sections, perso WHERE id_perso = $id AND sections.id_clan = perso.clan";
			$res = $mysqli->query($sql);
			while ($sec = $res->fetch_assoc()) {
				$id_section = $sec["id_section"];
				$nom_section = $sec["nom_section"];
				$image_section = $sec["image_section"];
				$resume_section = $sec["resume_section"];
				$description_section = $sec["description_section"];
						
				// creation des tableau avec les sections existantes
				echo "<table border=\"1\" width = 100%><tr>
				<td width=40 height=40><img src=\"".htmlspecialchars($image_section)."\" width=\"40\" height=\"40\"></td>
				<th width=25%>$nom_section</th>
				<td>".bbcode(htmlentities(stripslashes($resume_section)))."</td>
				<td width=80><a href='section.php?id_section=$id_section&voir_groupe=ok'><center>Plus d'infos</center></a></td>";
				echo "</tr></table>";
			}
		}
		else {
			// verification si le perso appartient déjà a une section
			$sql = "SELECT id_section FROM perso_in_section WHERE id_perso = '$id' and attenteValidation_section='0'";
			$res = $mysqli->query($sql);
			$c = $res->fetch_row();
			
			if ($c != 0) { // il appartient a une section
				
				// recuperation de la section a laquelle on appartient
				$id_section = $c[0];
				
				// recuperation des information sur la section
				$sql = "SELECT id_section, nom_section, image_section, resume_section, description_section FROM sections WHERE id_section=$id_section";
				$res = $mysqli->query($sql);
				$sec = $res->fetch_assoc();
				$id_section = $sec["id_section"];
				$nom_section = $sec["nom_section"];
				$image_section = $sec["image_section"];
				$resume_section = $sec["resume_section"];
				$description_section = $sec["description_section"];
					
				// affichage des information de la section
				echo "<center><b>$nom_section</b></center>";
				echo "<table border=\"1\" width = 100%><tr><td width=40 height=40><img src=\"".htmlspecialchars($image_section)."\" width=\"40\" height=\"40\"></td><td>".bbcode(htmlentities(stripslashes($resume_section)))."</td><td width=20%><center>Liste des membres</center></td>";
				echo "</tr><tr><td></td><td>".bbcode(htmlentities(stripslashes($description_section)))."</td><td>";
					
				// recuperation de la liste des membres de la section
				$sql = "SELECT nom_perso, poste_section FROM perso, perso_in_section WHERE perso_in_section.id_perso=perso.ID_perso AND id_section=$id_section AND attenteValidation_section='0' ORDER BY poste_section";
				$res = $mysqli->query($sql);
				while ($membre = $res->fetch_assoc()) {
					$nom_membre = $membre["nom_perso"];
					$poste_section = $membre["poste_section"];
					
					if($poste_section != 5){
						
						// recuperation du nom de poste
						$sql2 = "SELECT nom_poste FROM poste WHERE id_poste=$poste_section";
						$res2 = $mysqli->query($sql2);
						$t_p = $res2->fetch_assoc();
						$nom_poste = $t_p["nom_poste"];
						
						echo "<center>".$nom_membre." ($nom_poste)</center>";
					}
					else
						echo "<center>".$nom_membre."</center>";
				}
				echo "</td>";
				echo "</tr></table><br>";
				
				echo "<center><a href='banque_section.php?id_section=$id_section'>Deposer des sous à la banque du groupe</a></center>";
				
				// verification si le perso est le chef de la section
				$sql = "SELECT poste_section FROM perso_in_section WHERE id_perso=$id";
				$res = $mysqli->query($sql);
				$boss = $res->fetch_assoc();
				$poste_s = $boss["poste_section"];
				
				if($poste_s != 5) { // le perso à un poste
					if($poste_s == 1) { // c'est le chef
						echo "<center><a href='admin_section.php?id_section=$id_section'> Page d'administration du groupe</a></center>";
					}
					if($poste_s == 2){ // c'est le trèsorier
						// verification si quelqu'un a demandé un emprunt
						$sql = "SELECT banque_section.id_perso FROM banque_section, perso_in_section WHERE demande_emprunt='1' AND id_section=$id_section AND banque_section.id_perso=perso_in_section.id_perso";
						$res = $mysqli->query($sql);
						$nb = $res->num_rows;
						echo "<center><a href='tresor_section.php?id_section=$id_section'> Page tresorerie du groupe</a><font color=red>($nb persos en attente)</font></center>";
					}
					if($poste_s == 3 || $poste_s == 1){ // c'est le recruteur
						// on verifie si il y a des nouveau persos qui veulent integrer la section
						$sql = "SELECT nom_perso, perso_in_section.id_perso FROM perso_in_section, perso WHERE perso.ID_perso=perso_in_section.id_perso AND id_section=$id_section AND attenteValidation_section='1'";
						$res = $mysqli->query($sql);
						$num_a = $res->num_rows; // nombre de persos en attente
						echo "<center><a href='recrut_section.php?id_section=$id_section'> Page de recrutement du groupe</a><font color=red>($num_a persos en attente)</font></center>";
					}
					if($poste_s == 4){ // c'est le diplomate
						echo "<center><a href='diplo_section.php?id_section=$id_section'> Page diplomatie du groupe</a></center>";
					}
				}
				echo "<br/><center><a href='section.php?id_section=$id_section&rejoindre=off'"?> OnClick="return(confirm('Êtes vous sûr de vouloir quitter le groupe ?'))" <?php echo"><b> >>Quitter le groupe (ATTENTION : Cette action vous fera quitter definitivement le groupe)</b></a></center>";
				echo "<br/><br/><a href='section.php?voir_groupe=ok'>Voir les autres groupes</a>";
			}
			else { 
				// verification si le perso est en attente de validation
				$sql = "SELECT id_section FROM perso_in_section WHERE id_perso = '$id' and attenteValidation_section='1'";
				$res = $mysqli->query($sql);
				$c = $res->fetch_row();
			
				if(isset($_GET['annuler']) && $_GET['annuler']=='ok'){
					$sql ="delete from perso_in_section where id_perso='$id'";
					$mysqli->query($sql);
			
					echo "Vous venez d'annuler votre demande d'adhésion <br />";
					echo "<a href='section.php'>[ retour ]</>";
				}
				else{
					if ($c != 0) { // en attente de validation
						echo "Vous êtes en attente de validation pour un groupe";
						echo "<br/><a href='section.php?annuler=ok'>annuler sa candidature</a>";
						echo "<br/><br/><a href='section.php?voir_groupe=ok'>Voir les autres groupes</a>";
					}
					else { // il n'appartient a aucune section
				
						echo "<center><u>Vous pouvez créer un nouveau groupe si vous le souhaitez :</u></center>";
						echo "<form action=\"creer_section.php\" method=\"post\" name=\"section\">";
						echo "<div align=\"center\"><br>";
						echo "Nom du groupe : ";
						echo "<input name=\"section\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"50\">";
						echo "<input type=\"submit\" name=\"Submit\" value=\"ok\">";
						echo "</div>";
						echo "</form>";
					
						echo "<br/><center><b><u>Liste des groupes déjà existants</u></b></center><br/>";
						// recuperation des sections existantes
						$sql = "SELECT id_section, nom_section, image_section, resume_section, description_section FROM sections, perso WHERE id_perso = $id AND sections.id_clan = perso.clan";
						$res = $mysqli->query($sql);
						while ($sec = $res->fetch_assoc()) {
							$id_section = $sec["id_section"];
							$nom_section = $sec["nom_section"];
							$image_section = $sec["image_section"];
							$resume_section = $sec["resume_section"];
							$description_section = $sec["description_section"];
						
							// creation des tableau avec les sections existantes
							echo "<table border=\"1\" width = 100%><tr>
							<td width=40 height=40><img src=\"".htmlspecialchars($image_section)."\" width=\"40\" height=\"40\"></td>
							<th width=25%>$nom_section</th>
							<td>".bbcode(htmlentities(stripslashes($resume_section)))."</td>
							<td width=80><a href='section.php?id_section=$id_section'><center>Plus d'infos</center></a></td>
							<td width=100><a href='section.php?id_section=$id_section&rejoindre=ok'><center> >>Rejoindre</center></a></td>";
							echo "</tr></table>";
						}
					}
				}
			}
		}
	}
	?>
	</body>
	</html>
	<?php
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}?>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location: index2.php");
}
?>