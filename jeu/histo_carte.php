<?php
session_start();

if (isset($_SESSION["id_perso"])) {
	
	$id = $_SESSION["id_perso"];

	require_once "../fonctions.php";
	
	$mysqli = db_connexion();
	
	$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$camp_perso = $t['clan'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		
	</head>
	<body>
		<div class="container">
		
			<div class="row">
				<div class="col-12" align='center'>
					<a href='afficher_carte.php' class='btn btn-primary'>retour carte stratégique</a>
					<a href='jouer.php' class='btn btn-primary'>retour jeu</a>
					<a href='../index.php' class='btn btn-danger'>retour accueil</a>
				</div>
			</div>
		
			<?php	
			//nom du répertoire contenant les images à afficher 
			$nom_repertoire = './histo_carte/';
			
			//ouvre le repertoire
			$pointeur = opendir($nom_repertoire); 
			$i = 0; 
			
			//stocke les noms de fichiers images dans un tableau
			while ($fichier = readdir($pointeur)) {
				if (substr($fichier, -3) == "png") { 
					$tab_image[$i] = $fichier;
					$i++;
				}       
			}
			
			//on ferme le répertoire 
			closedir($pointeur); 

			//on trie le tableau par ordre alphabétique 
			array_multisort($tab_image, SORT_ASC);
			
			$taille_tab = count($tab_image);
			?>
			
			<div id="histo" class="carousel slide carousel-fade" data-ride="carousel" data-wrap="false" data-interval="false">

				<!-- Carrousel -->
				<div class="carousel-inner">
					<?php
					$compteur = 0;
					
					for ($j = 0; $j <= $taille_tab-1; $j++) {
						
						$nom_image = $tab_image[$j];
						
						$tab_nom_image = explode("_", $nom_image);
						$taille_tab_nom_image = count($tab_nom_image);
						
						if ($taille_tab_nom_image == 2) {
							
							$tab_camp_date_image = explode('-', $tab_nom_image[1]);
							
							$camp_image 		= $tab_camp_date_image[0];
							$date_annee_image 	= $tab_camp_date_image[1];
							$date_mois_image 	= $tab_camp_date_image[2];
							$date_jour_image 	= $tab_camp_date_image[3];
							
							if(($camp_perso == 1 && $camp_image == "nord") || ($camp_perso == 2 && $camp_image == "sud") || ($camp_perso == 3 && $camp_image == "indien")) {
							
								echo "<div class='carousel-item";
								if ($compteur == 0) {
									echo " active";
								}
								echo "'>";
								
								echo "	<img src='image_histo.php?imagename=".$nom_image."' alt='Carte vision du ".$camp_image." du".$date_jour_image."/".$date_mois_image."/".$date_annee_image."' class='d-block w-100'>";
								echo "	<div class='carousel-caption d-none d-md-block'>";
								echo "		<h5> Carte vision du ".$camp_image." du ".$date_jour_image."/".$date_mois_image."/".$date_annee_image."</h5>";
								echo "	</div>";
								
								echo "</div>";
								
								$compteur++;
							}
						}
					}
					?>
				</div>
				
				<!-- Contrôles -->
				<a class="carousel-control-prev" href="#histo" role="button" data-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="sr-only">Précédent</span>
				</a>
				<a class="carousel-control-next" href="#histo" role="button" data-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="sr-only">Suivant</span>
				</a>
			</div>
			
		</div>
	
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</body>
</html>
<?php
}
else {
	echo "Veuillez vous connecter";	
}
?>
