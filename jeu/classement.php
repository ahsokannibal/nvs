<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

$id = $_SESSION["id_perso"];

if (isset($_POST["choix_class"])){
	
	$num_class = $_POST["choix_class"];
	
	$verif = preg_match("#^[0-9]+$#i",$num_class);
		
	if($verif){
		header("Location:classement.php?top=ok&classement=$num_class");	
	}
	else {
		$erreur = 'Paramètre incorrect';
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud - Classement</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		
	</head>

	<body background="../images/background.jpg">

<?php
if(isset($erreur)){
	echo "<center><font color='red'>$erreur</font></center>";
}

if(isset($_GET["top"])){
	echo "<div align=\"center\"><h2><font color=darkred>Top 10</font></h2></div>";


	echo "<center><a href=\"classement.php\">Revenir au classement normal</a></center>";
	echo "<center><a href=\"classement.php?titre=ok\">Voir les Titres attribués aux persos</a></center>";
	echo "<center><a href=\"classement.php?training=ok\">Voir les pros de l'entrainement</a></center>";
	echo "<center><a href=\"classement.php?super=ok\">Voir les Supermans</a></center>";
	echo "<center><a href=\"classement.php?stats=ok\">Voir les Statistiques de chaque camps</a></center><br/>";
	
	echo "<center><form method=\"post\" action=\"classement.php\">";
	echo "Choix classement :";
	echo "<select name=\"choix_class\" onchange=\"this.form.submit()\">";
	echo "<OPTION value='1'"; if(isset($_GET["classement"]) && $_GET["classement"] == 1) echo " selected"; echo">kill (pk)</option>"; 
	echo "<OPTION value='2'"; if(isset($_GET["classement"]) && $_GET["classement"] == 2) echo " selected"; echo">death</option>"; 
	echo "<OPTION value='3'"; if(isset($_GET["classement"]) && $_GET["classement"] == 3) echo " selected"; echo">pnj</option>";
	echo "<OPTION value='4'"; if(isset($_GET["classement"]) && $_GET["classement"] == 4) echo " selected"; echo">or</option>";
	echo "<OPTION value='5'"; if(isset($_GET["classement"]) && $_GET["classement"] == 5) echo " selected"; echo">pv Max</option>";
	echo "<OPTION value='6'"; if(isset($_GET["classement"]) && $_GET["classement"] == 6) echo " selected"; echo">pm Max</option>";
	echo "<OPTION value='7'"; if(isset($_GET["classement"]) && $_GET["classement"] == 7) echo " selected"; echo">pa Max</option>";
	echo "<OPTION value='8'"; if(isset($_GET["classement"]) && $_GET["classement"] == 8) echo " selected"; echo">perception</option>";
	echo "<OPTION value='9'"; if(isset($_GET["classement"]) && $_GET["classement"] == 9) echo " selected"; echo">recup</option>";
	echo "</select>";
	echo "<input type=\"submit\" name=\"ch_c\" value=\"ok\">";
	echo "</form></center>";
	
	if(isset($_GET["classement"])) {
		$num_c = $_GET["classement"];
		$verif = preg_match("#^[0-9]+$#i",$num_c);
		
		if($verif){
			switch($num_c) {
				case 1:
					$class = "nb_kill";
					break;
				case 2:
					$class = "nb_mort";
					break;
				case 3:
					$class = "nb_pnj";
					break;
				case 4:
					$class = "or_perso";
					break;
				case 5:
					$class = "pvMax_perso";
					break;
				case 6:
					$class = "pmMax_perso";
					break;
				case 7:
					$class = "paMax_perso";
					break;
				case 8:
					$class = "perception_perso";
					break;
				case 9:
					$class = "recup_perso";
					break;
				default:
					$class = "nb_kill";
					break;
			}
		}
	}
	else {
		$class = "nb_kill";
	}
	
	if((isset($verif) && $verif) || !isset($_GET["classement"])){
	
		$sql = "SELECT id_perso, nom_perso, $class FROM perso WHERE id_perso > '10' ORDER BY $class DESC LIMIT 10";
		$res = $mysqli->query($sql);
		
		echo "<table align=center width='500' border=1> <tr><th><font color=darkred>position</font></th><th><font color=darkred>Nom[id]</font></th><th><font color=darkred>$class</font></th></tr>";
		$c = 0;
		
		if($class == "nb_kill") {
			echo "<center><h3><font color=darkred>Les Pks</font></h3></center>";
		}
		if($class == "nb_mort") {
			echo "<center><h3><font color=darkred>Les Cibles humaines</font></h3></center>";
		}
		if($class == "nb_pnj") {
			echo "<center><h3><font color=darkred>Les Killers</font></h3></center>";
		}
		if($class == "or_perso") {
			echo "<center><h3><font color=darkred>Les Riches</font></h3></center>";
		}
		if($class == "pvMax_perso") {
			echo "<center><h3><font color=darkred>Les Bons vivants</font></h3></center>";
		}
		if($class == "pmMax_perso") {
			echo "<center><h3><font color=darkred>Les Vagabonds</font></h3></center>";
		}
		if($class == "paMax_perso") {
			echo "<center><h3><font color=darkred>Les Hyperactifs</font></h3></center>";
		}
		if($class == "perception_perso") {
			echo "<center><h3><font color=darkred>Les Jumelles ambulantes</font></h3></center>";
		}
		if($class == "recup_perso") {
			echo "<center><h3><font color=darkred>Les Meilleures recup</font></h3></center>";
		}
			
		while($t = $res->fetch_assoc()){
			$c++;
			echo "<tr><td width=10><center>$c</center></td><td align=center>" .$t['nom_perso']. "[<a href=\"evenement.php?infoid=".$t['id_perso']."\">" .$t['id_perso']. "</a>]</td><td align=center width=150>".$t["$class"]."</td></tr>";
		}
		
		echo "</table>";
	}
	else {
		echo "<br /><center><b>Paramètre incorrect</b></center>";
	}
}
if(isset($_GET["titre"])){
	echo "<div align=\"center\"><h2><font color=darkred>Les Titres</font></h2></div>";

	echo "<center><a href=\"classement.php\">Revenir au classement normal</a></center>";
	echo "<center><a href=\"classement.php?top=ok\">Voir les tops 10</a></center>";
	echo "<center><a href=\"classement.php?training=ok\">Voir les pros de l'entrainement</a></center>";
	echo "<center><a href=\"classement.php?super=ok\">Voir les Supermans</a></center>";
	echo "<center><a href=\"classement.php?stats=ok\">Voir les Statistiques de chaque camps</a></center><br/>";
	
	$sql = "SELECT id_pnj FROM perso_as_killpnj GROUP BY id_pnj";
	$res = $mysqli->query($sql);
	
	echo "<table align=center width='500' border=1> <tr><th><font color=darkred>Nom[id]</font></th><th><font color=darkred>Titre</font></th></tr>";
	
	while ($t_pnj = $res->fetch_assoc()){
		$id_pnj = $t_pnj["id_pnj"];
		
		//echo "id_pnj : ".$id_pnj." ";
		
		$sql_p = "SELECT id_perso, id_pnj, nb_pnj FROM perso_as_killpnj WHERE nb_pnj=(SELECT MAX(nb_pnj) FROM perso_as_killpnj WHERE id_pnj=$id_pnj) AND id_pnj=$id_pnj";
		$res_p = $mysqli->query($sql_p);

		while($t = $res_p->fetch_assoc()){
			$id_perso_t =$t["id_perso"];
			$id_pnj_t = $t["id_pnj"];
			$nb_pnj_t =$t["nb_pnj"];
			
			//echo "id_perso : ".$id_perso_t." ";
			//echo "nb_pnj : ".$nb_pnj_t."<br/>";
			
			// recuperation du nom du perso
			$sql_n = "SELECT nom_perso FROM perso WHERE id_perso='$id_perso_t'";
			$res_n = $mysqli->query($sql_n);
			$t_n = $res_n->fetch_assoc();
			$nom_perso = $t_n["nom_perso"];
			
			// recuperation du nom du pnj
			$sql_n = "SELECT nom_pnj FROM pnj WHERE id_pnj='$id_pnj_t'";
			$res_n = $mysqli->query($sql_n);
			$t_n = $res_n->fetch_assoc();
			$nom_pnj = $t_n["nom_pnj"];
			
			if($nom_pnj == "Scaros le Cyclope"){
				$titre = $nom_perso." le Pourfendeur de Scaros le Cyclope";
			}
			
			if ($nom_pnj == "ogre") {
				$titre = $nom_perso." le Pourfendeur d'".$nom_pnj."s";
			}
			else {
				if ($nom_pnj == "dragon de terre") {
					$titre = $nom_perso." le Pourfendeur de dragons de terre";
				}
				else {
					if($nom_pnj == "cochon guerrier"){
						$titre = $nom_perso." le Pourfendeur de cochons guerriers";
					}
					else {
						$titre = $nom_perso." le Pourfendeur de ".$nom_pnj."s";
					}
				}
			}
			
			echo "<tr><td align=center>" .$nom_perso. "[<a href=\"evenement.php?infoid=".$id_perso_t."\">" .$id_perso_t. "</a>]</td><td align=center width=75%><b>".$titre."</></td></tr>";
		}
	}
	
	echo "</table>";
	
}
if(isset($_GET["stats"]) && $_GET["stats"] == 'ok'){
	echo "<div align=\"center\"><h2><font color=darkred>Statistiques</font></h2></div>";
	
	echo "<center><a href=\"classement.php\">Revenir au classement normal</a></center>";
	echo "<center><a href=\"classement.php?top=ok\">Voir les tops 10</a></center>";
	echo "<center><a href=\"classement.php?titre=ok\">Voir les Titres attribués aux persos</a></center>";
	echo "<center><a href=\"classement.php?training=ok\">Voir les pros de l'entrainement</a></center>";
	echo "<center><a href=\"classement.php?super=ok\">Voir les Supermans</a></center><br/>";
	
	// recuperation des stats
	$sql = "SELECT id_camp, nb_kill FROM stats_camp_kill";
	$res = $mysqli->query($sql);
	
	$sql_countb = "SELECT sum(nb_kill) as count_b FROM perso WHERE clan='1'";
	$res_countb = $mysqli->query($sql_countb);
	$t_b = $res_countb->fetch_assoc();
	$nb_countb = $t_b['count_b'];
	
	$sql_countr = "SELECT sum(nb_kill) as count_r FROM perso WHERE clan='2'";
	$res_countr = $mysqli->query($sql_countr);
	$t_r = $res_countr->fetch_assoc();
	$nb_countr = $t_r['count_r'];
	
	$sql_nbb = "SELECT id_perso FROM perso WHERE clan='1'";
	$res_nbb = $mysqli->query($sql_nbb);
	$nbb = $res_nbb->num_rows;
	
	$sql_nbr = "SELECT id_perso FROM perso WHERE clan='2'";
	$res_nbr = $mysqli->query($sql_nbr);
	$nbr = $res_nbr->num_rows;
	
	$sql_pvictb = "SELECT points_victoire FROM stats_camp_pv WHERE id_camp='1'";
	$res_pvictb = $mysqli->query($sql_pvictb);
	$t = $res_pvictb->fetch_assoc();
	$nbvictb = $t['points_victoire'];
	
	$sql_pvictr = "SELECT points_victoire FROM stats_camp_pv WHERE id_camp='2'";
	$res_pvictr = $mysqli->query($sql_pvictr);
	$t = $res_pvictr->fetch_assoc();
	$nbvictr = $t['points_victoire'];

	echo "<table align=center width='500' border=1>";
	echo "	<tr>";
	echo "		<th><font color=darkred>Camp[id]</font></th><th><font color=darkred>Nombre de captures ennemis</font></th><th><font color=darkred>Nombre de captures alliés</font></th><th><font color=darkred>Nombre de persos</font></th><th><font color=darkred>Points de victoires</font></th>";
	echo "	</tr>";

	while ($tc_kill = $res->fetch_assoc()){
		$id_camp = $tc_kill["id_camp"];
		$nb_kill = $tc_kill["nb_kill"];
		
		$meutre_b = $nb_countb - $nb_kill;
		$meutre_r = $nb_countr - $nb_kill;
		
		if($id_camp == "1"){
			$couleur_camp 	= "blue";
			$nom_camp 		= "Nord";
			$nb 			= $nbb;
			$meutre 		= $meutre_b;
			$pvict 			= $nbvictb;
		}
		if($id_camp == "2"){
			$couleur_camp 	= "red";
			$nom_camp 		= "Sud";
			$nb 			= $nbr;
			$meutre 		= $meutre_r;
			$pvict 			= $nbvictr;
		}
		if($id_camp == "3"){
			$couleur_camp = "green";
		}
		
		echo "<tr>";
		echo "	<td align=center><font color=\"$couleur_camp\">".$nom_camp."</font> [".$id_camp."]</td><td align=center>$nb_kill</td><td align=center>$meutre</td><td align=center>$nb</td><td align='center'>".$pvict."</td>";
		echo "</tr>";
	}
	echo "</table>";
}

if(isset($_GET['super']) && $_GET['super'] == 'ok'){
	echo "<div align=\"center\"><h2><font color=darkred>Les Supermans</font></h2></div>";
	
	echo "<center><a href=\"classement.php\">Revenir au classement normal</a></center>";
	echo "<center><a href=\"classement.php?top=ok\">Voir les tops 10</a></center>";
	echo "<center><a href=\"classement.php?titre=ok\">Voir les Titres attribués aux persos</a></center>";
	echo "<center><a href=\"classement.php?training=ok\">Voir les pros de l'entrainement</a></center>";
	echo "<center><a href=\"classement.php?stats=ok\">Voir les Statistiques de chaque camps</a></center><br/>";
	
	// bleus
	$sql = "SELECT 	max(id_grade) as id_grade_max, 
					max(xp_perso) as xp_max, 
					max(pvMax_perso) as pv_max, 
					max(pmMax_perso) as pm_max, 
					max(perception_perso) as perception_max, 
					max(recup_perso) as recup_max, 
					max(paMax_perso) as pa_max, 
					max(nb_kill) as kill_max, 
					max(nb_pnj) as pnj_max 
			FROM perso, perso_as_grade
			WHERE perso.id_perso>'10' 
			AND perso_as_grade.id_perso = perso.id_perso 
			AND perso_as_grade.id_grade != 101 AND perso_as_grade.id_grade != 102
			AND clan='1'";
	$res = $mysqli->query($sql);
	$t_b = $res->fetch_assoc();
	
	$id_grade_max_b = $t_b['id_grade_max'];
	$xp_max_b = $t_b['xp_max'];
	$pv_max_b = $t_b['pv_max'];
	$pm_max_b = $t_b['pm_max'];
	$perception_max_b = $t_b['perception_max'];
	$recup_max_b = $t_b['recup_max'];
	$pa_max_b = $t_b['pa_max'];
	$kill_max_b = $t_b['kill_max'];
	$pnj_max_b = $t_b['pnj_max'];
	
	$sql = "SELECT nom_grade FROM grades WHERE id_grade = '$id_grade_max_b'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$nom_grade_b = $t['nom_grade'];
	
	// rouges
	$sql = "SELECT 	max(id_grade) as id_grade_max, 
					max(xp_perso) as xp_max, 
					max(pvMax_perso) as pv_max, 
					max(pmMax_perso) as pm_max, 
					max(perception_perso) as perception_max, 
					max(recup_perso) as recup_max, 
					max(paMax_perso) as pa_max, 
					max(nb_kill) as kill_max, 
					max(nb_pnj) as pnj_max 
			FROM perso, perso_as_grade
			WHERE perso.id_perso>'10' 
			AND perso_as_grade.id_perso = perso.id_perso 
			AND perso_as_grade.id_grade != 101 AND perso_as_grade.id_grade != 102
			AND clan='2'";
	$res = $mysqli->query($sql);
	$t_r = $res->fetch_assoc();
	
	$id_grade_max_r = $t_r['id_grade_max'];
	$xp_max_r = $t_r['xp_max'];
	$pv_max_r = $t_r['pv_max'];
	$pm_max_r = $t_r['pm_max'];
	$perception_max_r = $t_r['perception_max'];
	$recup_max_r = $t_r['recup_max'];
	$pa_max_r = $t_r['pa_max'];
	$kill_max_r = $t_r['kill_max'];
	$pnj_max_r = $t_r['pnj_max'];
	
	$sql = "SELECT nom_grade FROM grades WHERE id_grade = '$id_grade_max_r'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$nom_grade_r = $t['nom_grade'];
	
	echo "<table align='center' border='1'>";
	echo "<tr><th>Nom</th><th>grade max</th><th>Xp</th><th>Pv</th><th>Pm</th><th>Perception</th><th>Recup</th><th>Pa</th><th>Nombre de kills</th><th>Nombre de pnj tués</th></tr>";
	echo "<tr><td align='center'><font color='blue'>Super Unioniste</font></td><td align='center'>$nom_grade_b <img src=\"../images/grades/" . $id_grade_max_b . ".gif\" /></td><td align='center'>$xp_max_b</td><td align='center'>$pv_max_b</td><td align='center'>$pm_max_b</td><td align='center'>$perception_max_b</td><td align='center'>$recup_max_b</td><td align='center'>$pa_max_b</td><td align='center'>$kill_max_b</td><td align='center'>$pnj_max_b</td></tr>";
	echo "<tr><td align='center'><font color='red'>Super Confédéré</font></td><td align='center'>$nom_grade_r <img src=\"../images/grades/" . $id_grade_max_r . ".gif\" /></td><td align='center'>$xp_max_r</td><td align='center'>$pv_max_r</td><td align='center'>$pm_max_r</td><td align='center'>$perception_max_r</td><td align='center'>$recup_max_r</td><td align='center'>$pa_max_r</td><td align='center'>$kill_max_r</td><td align='center'>$pnj_max_r</td></tr>";
	echo "</table>";
}

if(isset($_GET['training']) && $_GET['training'] == 'ok'){
	echo "<div align=\"center\"><h2><font color=darkred>Entrainement</font></h2></div>";
	
	echo "<center><a href=\"classement.php\">Revenir au classement normal</a></center>";
	echo "<center><a href=\"classement.php?top=ok\">Voir les tops 10</a></center>";
	echo "<center><a href=\"classement.php?titre=ok\">Voir les Titres attribués aux persos</a></center>";
	echo "<center><a href=\"classement.php?super=ok\">Voir les Supermans</a></center>";
	echo "<center><a href=\"classement.php?stats=ok\">Voir les Statistiques de chaque camps</a></center><br/>";
	
	$sql = "SELECT perso_as_entrainement.id_perso, nom_perso, niveau_entrainement, clan 
			FROM perso_as_entrainement, perso 
			WHERE perso_as_entrainement.id_perso=perso.id_perso 
			AND perso.id_perso>'10'
			ORDER BY niveau_entrainement DESC LIMIT 10";
	$res = $mysqli->query($sql);
	echo "<table align='center' border='1'>";
	echo "<tr><th>Nom</th><th>niveau entrainement</th><th>Gains surprise</th></tr>";
	while ($t = $res->fetch_assoc()){
		$nom = $t['nom_perso'];
		$niveau_e = $t['niveau_entrainement'];
		$camp = $t['clan'];
		
		if($camp == '1'){
			$color = 'blue';
		}
		if($camp == '2'){
			$color = 'red';
		}
		if($camp == '3'){
			$color = 'purple';
		}
	
		echo "<tr><td align='center'><font color=$color>$nom</font></td><td align='center'>$niveau_e</td><td align='center'>&nbsp;</td></tr>";
	}
	echo "</table>";
}


if(!isset($_GET["top"]) && !isset($_GET["titre"]) && !isset($_GET["stats"]) && !isset($_GET["super"]) && !isset($_GET["training"])) {
	echo "<div align=\"center\"><h2><font color=darkred>Classement</font></h2></div>";
	echo "<center><a href=\"classement.php?top=ok\">Voir les tops 10</a></center>";
	echo "<center><a href=\"classement.php?titre=ok\">Voir les Titres attribués aux persos</a></center>";
	echo "<center><a href=\"classement.php?training=ok\">Voir les pros de l'entrainement</a></center>";
	echo "<center><a href=\"classement.php?super=ok\">Voir les Supermans</a></center>";
	echo "<center><a href=\"classement.php?stats=ok\">Voir les Statistiques de chaque camps</a></center><br/>";
	
	// recuperation des valeurs en excluant les persos pnj
	$sql = "SELECT perso.id_perso, nom_perso, xp_perso, clan, nom_grade FROM perso, perso_as_grade, grades 
			WHERE perso.id_perso = perso_as_grade.id_perso 
			AND perso_as_grade.id_grade = grades.id_grade
			AND perso.id_perso > '10'
			ORDER BY xp_perso DESC";
	$res = $mysqli->query($sql);
	echo "<table align=center width='500' border=1> <tr><th><font color=darkred>position</font></th><th><font color=darkred>Nom[id]</font></th><th><font color=darkred>xp</font></th><th><font color=darkred>niveau</font></th></tr>";
	$cc = 0;
	while ($t2 = $res->fetch_assoc()){
		$id_camp = $t2["clan"];
		if($id_camp == "1"){
			$couleur_camp = "blue";
		}
		if($id_camp == "2"){
			$couleur_camp = "red";
		}
		if($id_camp == "3"){
			$couleur_camp = "green";
		}
		$cc++;
		echo "<tr><td width=10>$cc</td>";
		echo "<td align=center><font color=$couleur_camp>".$t2['nom_perso']."</font>[<a href=\"evenement.php?infoid=".$t2['id_perso']."\">" .$t2['id_perso']. "</a>]</td>";
		echo "<td align=center>".$t2['xp_perso']."</td>";
		echo "<td align=center>".$t2['nom_grade']."</td></tr>";
	}
	
	echo "</table>";
}
?>
	</body>
</html>