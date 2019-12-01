<?php 
session_start();
require_once("../fonctions.php");
require_once("../jeu/f_carte.php");

$mysqli = db_connexion();

if (isset ($_POST['liste_x']) && isset ($_POST['liste_y']) && isSet ($_POST['perception']))
{
	$x_choix = $_SESSION['x_choix'] = $_POST['liste_x'];
	$y_choix = $_SESSION['y_choix'] = $_POST['liste_y'];
	$perc = $_SESSION['perc'] = $_POST['perception'];
}

if (isset ($_POST['liste_x_min']) && isset ($_POST['liste_y_min']) && isset ($_POST['liste_x_max']) && isset ($_POST['liste_y_max']))
{
	$x_choix_min = $_SESSION['x_choix_min'] = $_POST['liste_x_min'];
	$y_choix_min = $_SESSION['y_choix_min'] = $_POST['liste_y_min'];
	$x_choix_max = $_SESSION['x_choix_max'] = $_POST['liste_x_max'];
	$y_choix_max = $_SESSION['y_choix_max'] = $_POST['liste_y_max'];
	$terrain_choix = $_SESSION['terrain_choix'] = $_POST['liste_terrain'];
}

if(isset($_POST['eval_choix_carte']) && $_POST['eval_choix_carte'] == "ok"){
	$carte = $_SESSION['choix_carte'] = $_POST['choix_carte'];
	
	if($carte == "arene"){
		$Y_MAXD = 20;
		$X_MAXD = 20;
	}
	else {
		$Y_MAXD = 200;
		$X_MAXD = 200;
	}
}
else {
	$Y_MAXD = 200;
	$X_MAXD = 200;
}
?>
<html>
<head>
<title>utilitaire de creation de cartes</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

<form method="post" action="utils_carte.php">
<u>Choix de la carte à modifier :</u><br>
<select name="choix_carte">
	<OPTION value="carte">carte de jeu</option>
</select>
<input type="submit" name="eval_choix_carte" value="ok">
</form>

<form method="post" action="utils_carte.php">
<u>Creation d'un carre de terrain :</u><br>
x_min :
<select name="liste_x_min">
<?php
for ($x_min = X_MIN; $x_min <= $X_MAXD; $x_min++)
{
	echo "<OPTION value=".$x_min." ";
	if (isSet($_SESSION['x_choix_min']) && $_SESSION['x_choix_min'] == $x_min) {
		echo " selected";
	}
	echo ">".$x_min."</option>";
}
?>
</select>
x_max :
<select name="liste_x_max">
<?php
for ($x_max = X_MIN; $x_max <= $X_MAXD; $x_max++)
{
	echo "<OPTION value=".$x_max." ";
	if (isSet($_SESSION['x_choix_max']) && $_SESSION['x_choix_max'] == $x_max) {
		echo " selected";
	}
	echo ">".$x_max."</option>";
}
?>
</select>
y_min :
<select name="liste_y_min">
<?php
for ($y_min = Y_MIN; $y_min <= $Y_MAXD; $y_min++)
{
	echo "<OPTION value=".$y_min." ";
	if (isSet($_SESSION['y_choix_min']) && $_SESSION['y_choix_min'] == $y_min) {
		echo " selected";
	}
	echo ">".$y_min."</option>";
}
?>
</select>
y_max :
<select name="liste_y_max">
<?php
for ($y_max = Y_MIN; $y_max <= $Y_MAXD; $y_max++)
{
	echo "<OPTION value=".$y_max." ";
	if (isSet($_SESSION['y_choix_max']) && $_SESSION['y_choix_max'] == $y_max) {
		echo " selected";
	}
	echo ">".$y_max."</option>";
}
?>
</select>
terrain :
<select name="liste_terrain">
<?php
for ($i = 1; $i <= 9; $i++)
{
	echo "<OPTION value=".$i." ";
	if (isSet($_SESSION['terrain_choix']) && $_SESSION['terrain_choix'] == $i)
		echo " selected";
	switch($i) {
		case(1): echo ">plaine</option>";
		case(2): echo ">colline</option>";
		case(3): echo ">montagne</option>";
		case(4): echo ">desert</option>";
		case(5): echo ">neige</option>";
		case(6): echo ">marecage</option>";
		case(7): echo ">foret</option>";
		case(8): echo ">eau</option>";
		case(9): echo ">eau_profonde</option>";
		}
}
?>
</select>
<input type="submit" name="eval_pate" value="ok">
</form>
<form method="post" action="utils_carte.php">
<u>choix coordonnées et perception :</u><br>
Choix coordonnée x :
<select name="liste_x">
<?php
for ($x = X_MIN; $x <= $X_MAXD; $x++)
{
	echo "<OPTION value=".$x." ";
	if (isSet($_SESSION['x_choix']) && $_SESSION['x_choix'] == $x) {
		echo " selected";
	}
	echo ">".$x."</option>";
}
?>
</select>
Choix coordonnée y :
<select name="liste_y">
<?php
for ($y = Y_MIN; $y <= $Y_MAXD; $y++)
{
	echo "<OPTION value=".$y." ";
	if (isSet($_SESSION['y_choix']) && $_SESSION['y_choix'] == $y) {
		echo " selected";
	}
	echo ">".$y."</option>";
}
?>
</select>
Perception :
<select name="perception">
<?php
for ($p = 1; $p <= max($X_MAXD, $Y_MAXD); $p++)
{
	echo "<OPTION value=".$p." ";
	if (isSet($_SESSION['perc']) && $_SESSION['perc'] == $p) {
		echo " selected";
	}
	echo ">".$p."</option>";
}
?>
</select>
<input type="submit" name="eval_xy" value="ok">
</form>
<BR>
<form method="post" action="utils_carte.php">
	<table width="100%" border="5">
	
		<tr>
			<td width="6%"><b><font color="#660000">Terrains:</font></b></td>
			<td> 
				<input type="radio" name="terrain" value="1.gif" id="plaine">
				<img src="../fond_carte/1.gif" width="34" height="34"><br>Plaine
			</td>
			<td> 
				<input type="radio" name="terrain" value="2.gif" id="colline">
				<img src="../fond_carte/2.gif" width="34" height="34"><br>Colline
			</td>
			<td> 
				<input type="radio" name="terrain" value="3.gif" id="montagne">
				<img src="../fond_carte/3.gif" width="34" height="34"><br>Montagne
			</td>
			<td> 
				<input type="radio" name="terrain" value="4.gif" id="desert">
				<img src="../fond_carte/4.gif" width="34" height="34"><br>Desert
			</td>
			<td> 
				<input type="radio" name="terrain" value="5.gif" id="neige">
				<img src="../fond_carte/5.gif" width="34" height="34"><br>Neige
			</td>
			<td> 
				<input type="radio" name="terrain" value="6.gif" id="plaine">
				<img src="../fond_carte/6.gif" width="34" height="34"><br>Marecage
			</td>
			<td> 
				<input type="radio" name="terrain" value="7.gif" id="foret">
				<img src="../fond_carte/7.gif" width="34" height="34"><br>Foret
			</td>
			<td> 
				<input type="radio" name="terrain" value="8.gif" id="eau">
				<img src="../fond_carte/8.gif" width="34" height="34"><br>Eau
			</td>
			<td> 
				<input type="radio" name="terrain" value="9.gif" id="eau_p">
				<img src="../fond_carte/9.gif" width="34" height="34"><br>Eau_profonde
			</td>
		</tr>
		
		<tr>
			<td width="6%"><b><font color="#660000">Batiments:</font></b></td>
			<td> 
				<input type="radio" name="batiment" value="b1b.png" id="barricade_bleu">
				<img src="../images_perso/b1b.png" width="34" height="34"><br>Barricade Bleu
			</td>
			<td> 
				<input type="radio" name="batiment" value="b1r.png" id="barricade_rouge">
				<img src="../images_perso/b1r.png" width="34" height="34"><br>Barricade Rouge
			</td>
			<td> 
				<input type="radio" name="batiment" value="b2b.png" id="tour_guet_bleu">
				<img src="../images_perso/b2b.png" width="34" height="34"><br>Tour de guet Bleu
			</td>
			<td> 
				<input type="radio" name="batiment" value="b2r.png" id="tour_guet_rouge">
				<img src="../images_perso/b2r.png" width="34" height="34"><br>Tour de guet Rouge
			</td>
			<td> 
				<input type="radio" name="batiment" value="b5b.png" id="pont_rouge">
				<img src="../images_perso/b5b.png" width="34" height="34"><br>Pont Rouge
			</td>
			<td> 
				<input type="radio" name="batiment" value="b5r.png" id="pont_bleu">
				<img src="../images_perso/b5r.png" width="34" height="34"><br>Pont Bleu
			</td>
			<td> 
				<input type="radio" name="batiment" value="b7b.png" id="hopital_bleu">
				<img src="../images_perso/b7b.png" width="34" height="34"><br>Hopital Bleu
			</td>
			<td> 
				<input type="radio" name="batiment" value="b7r.png" id="hopital_rouge">
				<img src="../images_perso/b7r.png" width="34" height="34"><br>Hopital Rouge
			</td>
			<td> 
				<input type="radio" name="batiment" value="b6b.png" id="fortin_bleu">
				<img src="../images_perso/b6b.png" width="34" height="34"><br>Fortin Bleu
			</td>
			<td> 
				<input type="radio" name="batiment" value="b6r.png" id="fortin_rouge">
				<img src="../images_perso/b6r.png" width="34" height="34"><br>Fortin Rouge
			</td>
			<td> 
				<input type="radio" name="batiment" value="b9b.png" id="fort_bleu">
				<img src="../images_perso/b9b.png" width="34" height="34"><br>Fort Bleu
			</td>
			<td> 
				<input type="radio" name="batiment" value="b9r.png" id="fort_rouge">
				<img src="../images_perso/b9r.png" width="34" height="34"><br>Fort Rouge
			</td>
			<td> 
				<input type="radio" name="batiment" value="b11b.gif" id="gare_bleu">
				<img src="../images_perso/b11b.gif" width="34" height="34"><br>Gare Bleu
			</td>
			<td> 
				<input type="radio" name="batiment" value="b11r.gif" id="gare_rouge">
				<img src="../images_perso/b11r.gif" width="34" height="34"><br>Gare Rouge
			</td>
		</tr>
		
		<tr>
			<td width="6%"><b><font color="#660000">pnj et autre :</font></b></td>
			<td> 
				<input type="radio" name="pnj" value="1" id="sangsue">
				<img src="../images/pnj/pnj1t.png" width="34" height="34"><br>Sangsue
			</td>
			<td>
				<input type="radio" name="pnj" value="2" id="loup">
				<img src="../images/pnj/pnj2t.gif" width="34" height="34"><br>Loup
			</td>
			<td>
				<input type="radio" name="pnj" value="3" id="crotale">
				<img src="../images/pnj/pnj3t.gif" width="34" height="34"><br>Crotale
			</td>
			<td>
				<input type="radio" name="pnj" value="4" id="caiman">
				<img src="../images/pnj/pnj4t.png" width="34" height="34"><br>Caïman
			</td>
			<td>
				<input type="radio" name="pnj" value="5" id="bison">
				<img src="../images/pnj/pnj5t.png" width="34" height="34"><br>Bison
			</td>
			<td>
				<input type="radio" name="pnj" value="6" id="bison_blanc">
				<img src="../images/pnj/pnj6t.gif" width="34" height="34"><br>Bison blanc
			</td>
			<td>
				<input type="radio" name="pnj" value="7" id="scorpion">
				<img src="../images/pnj/pnj7t.png" width="34" height="34"><br>Scorpion
			</td>
			<td>
				<input type="radio" name="pnj" value="8" id="aigle">
				<img src="../images/pnj/pnj8t.png" width="34" height="34"><br>Aigle
			</td>
			<td>
				<input type="radio" name="pnj" value="9" id="ours">
				<img src="../images/pnj/pnj9t.png" width="34" height="34"><br>Ours
			</td>
			<td>
				<input type="radio" name="pnj" value="10" id="mur">
				<img src="../images_perso/murt.png" width="34" height="34"><br>Mur
			</td>
			<td>
				<input type="radio" name="pnj" value="11" id="coffre">
				<img src="../images_perso/coffre1t.png" width="34" height="34"><br>Coffre
			</td>	
		</tr>
	</table>
<?php
if (isset($_POST["eval_pate"]) && $_POST["eval_pate"] == "ok"){
	
	if(isset ($_POST['liste_x_min']) || isset ($_POST['liste_y_min']) || isset ($_POST['liste_x_max']) || isset ($_POST['liste_y_max'])){
		
		$x_choix_min = $_SESSION['x_choix_min'] = $_POST['liste_x_min'];
		$y_choix_min = $_SESSION['y_choix_min'] = $_POST['liste_y_min'];
		$x_choix_max = $_SESSION['x_choix_max'] = $_POST['liste_x_max'];
		$y_choix_max = $_SESSION['y_choix_max'] = $_POST['liste_y_max'];
		$terrain_choix = $_SESSION['terrain_choix'] = $_POST['liste_terrain'];
		
		$image_terrain = "$terrain_choix.gif";
		
		$sql = "UPDATE carte SET fond_carte='$image_terrain' WHERE x_carte>='$x_choix_min' AND x_carte<='$x_choix_max' AND y_carte>='$y_choix_min' AND y_carte<='$y_choix_max'";
		$mysqli->query($sql);
	}
}

if (isset ($_POST['terrain']) && isset ($_POST['case']))
{
	$tabcase = $_POST['case'];
	$terrain = $_POST['terrain'];
	
	for ($i = 0; $i < count($tabcase); $i++) {
		
		$j = 0;
		$tabcase_x = $tabcase[$i][$j++];
		$stop = $tabcase[$i][$j];
		
		while ($stop != 's')
		{
			$tabcase_x .= $tabcase[$i][$j++];
			$stop = $tabcase[$i][$j];
		}
		
		$j++;
		$tabcase_y = $tabcase[$i][$j++];
		$stop = $tabcase[$i][$j];
		
		while ($stop != 's')
		{
			$tabcase_y .= $tabcase[$i][$j++];
			$stop = $tabcase[$i][$j];
		}
		
		if(isset($_SESSION['choix_carte'])){
			
			$carte = $_SESSION['choix_carte'];
			
			if($carte == 'arene'){
				$sql = "UPDATE arene SET fond_carte='$terrain' WHERE x_carte=$tabcase_x AND y_carte=$tabcase_y";
				$mysqli->query($sql);
			}
			else {
				$sql = "UPDATE carte SET fond_carte='$terrain' WHERE x_carte=$tabcase_x AND y_carte=$tabcase_y";
				$mysqli->query($sql);
			}
		}
		else {
			$sql = "UPDATE carte SET fond_carte='$terrain' WHERE x_carte=$tabcase_x AND y_carte=$tabcase_y";
			$mysqli->query($sql);
		}
	}
}

if (isSet($_POST['pnj']) && isset ($_POST['case'])){
	
	$tabcase = $_POST['case'];
	$pnj = $_POST['pnj'];
	
	for ($i = 0; $i < count($tabcase); $i++) {
		
		$j = 0;
		$tabcase_x = $tabcase[$i][$j++];
		$stop = $tabcase[$i][$j];
		
		while ($stop != 's')
		{
			$tabcase_x .= $tabcase[$i][$j++];
			$stop = $tabcase[$i][$j];
		}
		
		$j++;
		$tabcase_y = $tabcase[$i][$j++];
		$stop = $tabcase[$i][$j];
		
		while ($stop != 's')
		{
			$tabcase_y .= $tabcase[$i][$j++];
			$stop = $tabcase[$i][$j];
		}
		
		if(isset($_SESSION['choix_carte'])){
			
			$carte = $_SESSION['choix_carte'];
			
			
			// verification si la case est deja occupee ou non
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
				
			$oc = $t["occupee_carte"];
			
			if($oc) {
				echo "impossible de placer le pnj a cet endroit : la case est déjà occuppée<br>";
			}
			else {
				if ($pnj <= 9) {
					
					// recuperation des pv du pnj
					$sql = "SELECT pvMax_pnj, nom_pnj, pm_pnj FROM pnj WHERE id_pnj='$pnj'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$pvMaxPnj 	= $t["pvMax_pnj"];
					$nomPnj 	= $t["nom_pnj"];
					$pmPnj 		= $t["pm_pnj"];
					
					// creation du pnj
					$sql = "INSERT INTO instance_pnj (id_pnj, pv_i, pm_i, deplace_i, dernierAttaquant_i, x_i, y_i, bonus_i) VALUES ('$pnj','$pvMaxPnj','$pmPnj','1',0,'$tabcase_x','$tabcase_y','0')";
					$mysqli->query($sql);
					$id_instance = $mysqli->insert_id;
					
					// on met le pnj sur la carte
					$image_pnj = "pnj".$pnj."t.png";
					$sql = "UPDATE carte SET occupee_carte = '1', idPerso_carte='$id_instance', image_carte='$image_pnj' WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
					$mysqli->query($sql);
				}
				else {
					if($pnj == 10){
						$nomPnj = "mur";
						$image = "murt.png";
					}
					if($pnj == 11){
						$nomPnj = "coffre";
						$image = "coffre1t.png";
					}
					$sql = "UPDATE carte SET occupee_carte='1', image_carte='$image' WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
					$mysqli->query($sql);	
				}
			
				echo "Vous avez placer un $nomPnj en :<br>";
				echo "$tabcase_x/$tabcase_y<br>";
			}				
		}
		else { // par defaut : carte normale
		
			// verification si la case est deja occupee ou non
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$oc = $t["occupee_carte"];
				
			if($oc) {
				echo "impossible de placer le pnj a cet endroit : la case est déjà occuppée<br>";
			}
			else {
				if ($pnj <= 9) {	
					// recuperation des pv du pnj
					$sql = "SELECT pvMax_pnj, nom_pnj, pm_pnj FROM pnj WHERE id_pnj='$pnj'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$pvMaxPnj = $t["pvMax_pnj"];
					$nomPnj = $t["nom_pnj"];
					$pmPnj = $t["pm_pnj"];
					
					// creation du pnj
					$sql = "INSERT INTO instance_pnj (id_pnj, pv_i, pm_i, deplace_i, dernierAttaquant_i, x_i, y_i, bonus_i) VALUES ('$pnj','$pvMaxPnj','$pmPnj','1','','$tabcase_x','$tabcase_y','0')";
					$mysqli->query($sql);
					$id_instance = $mysqli->insert_id;
					
					// on met le pnj sur la carte
					$image_pnj = "pnj".$pnj."t.png";
					$sql = "UPDATE carte SET occupee_carte = '1', idPerso_carte='$id_instance', image_carte='$image_pnj' WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
					$mysqli->query($sql);
				}
				else {
					if($pnj == 10){
						$nomPnj = "mur";
						$image = "murt.png";
					}
					if($pnj == 11){
						$nomPnj = "coffre";
						$image = "coffre1t.png";
					}
					$sql = "UPDATE carte SET occupee_carte='1', image_carte='$image' WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
					$mysqli->query($sql);
				}
			
				echo "Vous avez placer un $nomPnj en :<br>";
				echo "$tabcase_x/$tabcase_y<br>";
			}
		}
	}
}

if (isset($_POST['eval_xy']) && $_POST['eval_xy'] == "ok")
{
	if (isset ($_POST['liste_x']) && isset ($_POST['liste_y']) && isSet ($_POST['perception']))
	{
		$x_choix = $_SESSION['x_choix'] = $_POST['liste_x'];
		$y_choix = $_SESSION['y_choix'] = $_POST['liste_y'];
		$perc = $_SESSION['perc'] = $_POST['perception'];
		
		echo '<table border=1 align="left">';
		echo "<tr><td width=40 height=40>y / x</td>";  //affichage des abscisses
		for ($i = $x_choix - $perc; $i <= $x_choix + $perc; $i++) 
		{
			if ($i == $x_choix)
			{
				echo "<th class=\"map\" bgcolor=\"#cccccc\">$i</th>";
			}
			else
			{
				echo "<th class=\"map\">$i</th>";
			}
		}
		echo "</tr>";
		
		if(isset($_SESSION['choix_carte'])){
			
			$carte = $_SESSION['choix_carte'];
			
			if($carte == "arene"){
				$sql = "SELECT x_carte, y_carte, fond_carte, idPerso_carte, image_carte, occupee_carte FROM arene WHERE x_carte >= $x_choix - $perc AND x_carte <= $x_choix + $perc AND y_carte <= $y_choix + $perc AND y_carte >= $y_choix - $perc ORDER BY y_carte DESC, x_carte";
				$res = $mysqli->query($sql);
				$tab = $res->fetch_assoc();
			}
			else {
			
				$sql = "SELECT x_carte, y_carte, fond_carte, idPerso_carte, image_carte, occupee_carte FROM carte WHERE x_carte >= $x_choix - $perc AND x_carte <= $x_choix + $perc AND y_carte <= $y_choix + $perc AND y_carte >= $y_choix - $perc ORDER BY y_carte DESC, x_carte";
				$res = $mysqli->query($sql);
				$tab = $res->fetch_assoc();
			}
		}
		else { // par defaut on met la carte normale
		
			$sql = "SELECT x_carte, y_carte, fond_carte, idPerso_carte, image_carte, occupee_carte FROM carte WHERE x_carte >= $x_choix - $perc AND x_carte <= $x_choix + $perc AND y_carte <= $y_choix + $perc AND y_carte >= $y_choix - $perc ORDER BY y_carte DESC, x_carte";
			$res = $mysqli->query($sql);
			$tab = $res->fetch_assoc();
		}
		
		for ($y = $y_choix + $perc; $y >= $y_choix - $perc; $y--) 
		{
			echo "<tr align=\"center\">";
			if ($y == $y_choix)
			{
				echo "<th width=40 height=40 bgcolor=\"#cccccc\">$y</b></th>";
			}
			else
			{
				echo "<th width=40 height=40>$y</b></th>";
			}
			for ($x = $x_choix - $perc; $x <= $x_choix + $perc; $x++) 
			{
				if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAXD && $y <= $Y_MAXD) 
				{ //les coordonnees sont dans les limites
					if ($x == $x_choix && $y == $y_choix) //coordonnees du mileu
					{
						if($tab["occupee_carte"])
							echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40><input type=\"checkbox\" name=\"case[]\" value=\"".$x."s".$y."s"."\">".$tab["x_carte"]."/".$tab["y_carte"]."</td>"; //positionnement du milieu
						else
							echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><input type=\"checkbox\" name=\"case[]\" value=\"".$x."s".$y."s"."\">".$tab["x_carte"]."/".$tab["y_carte"]."</td>"; //positionnement du milieu
					}
					else
					{	
						if($tab["occupee_carte"])
							echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40><input type=\"checkbox\" name=\"case[]\" value=\"".$x."s".$y."s"."\">".$tab["x_carte"]."/".$tab["y_carte"]."</td>"; //positionnement du milieu
						else
							echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><input type=\"checkbox\" name=\"case[]\" value=\"".$x."s".$y."s"."\">".$tab["x_carte"]."/".$tab["y_carte"]."</td>"; //positionnement du fond
					}
					$tab = $res->fetch_assoc();
				}
				else //les coordonnees sont hors limites
				{
					echo "<td width=40 height=40 background=\"../fond_carte/decorO.jpg\">$x/$y</td>";
				}
			}
			echo "</tr>";
		}		
	}
}
elseif (isSet($_SESSION['x_choix']) && isSet($_SESSION['y_choix']) && isSet($_SESSION['perc']))
{
	$x_choix = $_SESSION['x_choix'];
	$y_choix = $_SESSION['y_choix'];
	$perc = $_SESSION['perc'];

	echo '<table border=1 align="left">';
	echo "<tr><td width=40 height=40>y / x</td>";  //affichage des abscisses
	for ($i = $x_choix - $perc; $i <= $x_choix + $perc; $i++) 
	{
		if ($i == $x_choix)
		{
			echo "<th class=\"map\" bgcolor=\"#cccccc\">$i</th>";
		}
		else
		{
			echo "<th class=\"map\">$i</th>";
		}
	}
	echo "</tr>";
	
	if(isset($_SESSION['choix_carte'])){
		$carte = $_SESSION['choix_carte'];
			
		if($carte == "arene"){
			$sql = "SELECT x_carte, y_carte, fond_carte, idPerso_carte, image_carte, occupee_carte FROM arene WHERE x_carte >= $x_choix - $perc AND x_carte <= $x_choix + $perc AND y_carte <= $y_choix + $perc AND y_carte >= $y_choix - $perc ORDER BY y_carte DESC, x_carte";
			$res = $mysqli->query($sql);
			$tab = $res->fetch_assoc();
		}
		else {
		
			$sql = "SELECT x_carte, y_carte, fond_carte, idPerso_carte, image_carte, occupee_carte FROM carte WHERE x_carte >= $x_choix - $perc AND x_carte <= $x_choix + $perc AND y_carte <= $y_choix + $perc AND y_carte >= $y_choix - $perc ORDER BY y_carte DESC, x_carte";
			$res = $mysqli->query($sql);
			$tab = $res->fetch_assoc();
		}
	}
	else { // par defaut on met la carte normale
	
		$sql = "SELECT x_carte, y_carte, fond_carte, idPerso_carte, image_carte, occupee_carte FROM carte WHERE x_carte >= $x_choix - $perc AND x_carte <= $x_choix + $perc AND y_carte <= $y_choix + $perc AND y_carte >= $y_choix - $perc ORDER BY y_carte DESC, x_carte";
		$res = $mysqli->query($sql);
		$tab = $res->fetch_assoc();
	}
	
	for ($y = $y_choix + $perc; $y >= $y_choix - $perc; $y--) {
		
		echo "<tr align=\"center\">";
		
		if ($y == $y_choix) {
			echo "<th width=40 height=40 bgcolor=\"#cccccc\">$y</b></th>";
		}
		else {
			echo "<th width=40 height=40>$y</b></th>";
		}
		
		for ($x = $x_choix - $perc; $x <= $x_choix + $perc; $x++) {
			
			//les coordonnees sont dans les limites
			if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAXD && $y <= $Y_MAXD) { 
			
				if($tab["occupee_carte"]) {
					
					$dossier_image = "images_perso";
					
					if ($tab["idPerso_carte"] >= 200000) {
						// PNJ
						$dossier_image = "images/pnj";
					}
					
					//positionnement du milieu
					echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../". $dossier_image ."/" . $tab["image_carte"] . "\" width=40 height=40><input type=\"checkbox\" name=\"case[]\" value=\"".$x."s".$y."s"."\">" . $tab["x_carte"] . "/" . $tab["y_carte"] . "</td>";
				}
				else {
					//positionnement du fond
					echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><input type=\"checkbox\" name=\"case[]\" value=\"".$x."s".$y."s"."\">".$tab["x_carte"]."/".$tab["y_carte"]."</td>"; 
				}
				
				$tab = $res->fetch_assoc();
			}
			else {
				//les coordonnees sont hors limites
				echo "<td width=40 height=40 background=\"../fond_carte/decorO.jpg\">$x/$y</td>";
			}
		}
		echo "</tr>";
	}
}
?>
<input type="submit" name="eval_terrain" value="appliquer">
</form>
</body>
</html>