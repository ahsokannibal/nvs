<?php
session_start();
require_once("fonctions.php");

$mysqli = db_connexion();

include ('nb_online.php');

// récupération nombre de joueurs inscrit
$sql = "SELECT id_joueur FROM joueur";
$res = $mysqli->query($sql);
$nb_inscrit = $res->num_rows;

// dernier inscrit
$sql = "SELECT nom_perso, clan FROM perso, joueur 
		WHERE perso.idJoueur_perso = joueur.id_joueur 
		ORDER BY id_joueur DESC, id_perso ASC LIMIT 1";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$pseudo_last_inscrit 	= $t['nom_perso'];
$clan_last_inscrit 		= $t['clan'];

// Nombre de persos actifs 
$sql = "SELECT count(id_perso) as nb_persos_actifs FROM perso WHERE est_gele='0'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$nb_persos_actifs = $t['nb_persos_actifs'];

// Nombre de persos actif nordistes
$sql = "SELECT count(id_perso) as nb_persos_nord_actifs FROM perso WHERE est_gele='0' AND clan='1'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$nb_persos_nord_actifs = $t['nb_persos_nord_actifs'];

// Nombre de persos actif sudistes
$sql = "SELECT count(id_perso) as nb_persos_sud_actifs FROM perso WHERE est_gele='0' AND clan='2'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$nb_persos_sud_actifs = $t['nb_persos_sud_actifs'];


// on prépare une requête SQL permettant de compter le nombre de tuples (soit le nombre de clients connectés au site) contenu dans la table
$sql = 'SELECT count(*) FROM nb_online';
$res = $mysqli->query($sql);
$count_online = $res->fetch_array();

?>
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>

	<body style="background-color:grey;">

		<div class="container-fluid">
			
			<div class="row">
				<div class="col-4">
					<img src="images/accueil/logo_NVS_lee.png" alt='baniere NVS' class="img-fluid" />
				</div>
				<div class="col-8">
					<div align='center'><img src="images/accueil/baniere_accueil.jpg" alt='baniere NVS' class="img-fluid" /></div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
				&nbsp;
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div class="row">
						<div class="col-sm-4">
							<font color='red'><b>Jeu en cours de mise à jour, veuillez patienter</b></font>
							<hr />
							<a href="faq.php" style="color: white;">FAQ - Régles</b></a>
							<hr />
							<a href="http://nordvssud-creation.forumactif.com/" style="color: white;">Le Forum</b></a>
							<hr />
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
								<input type="hidden" name="cmd" value="_s-xclick" />
								<input type="hidden" name="hosted_button_id" value="YRKPHY4WX37F6" />
								<input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Bouton Faites un don avec PayPal" />
								<img alt="" border="0" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1" />
							</form>
						</div>
						
						<div class="col-8">
						
							<div class='d-none d-md-block d-lg-block d-xl-block'>
								<table border='1' width='100%'>
									<tr>
										<th bgcolor="#FFFACD" style="text-align: center;">Quelques informations</th>
									</tr>
									<tr>
										<td><b>Nombre de joueurs inscrit : <?php echo $nb_inscrit; ?> <br />Nombre de joueurs connectés : <?php echo $count_online[0];?><br />Dernier inscrit : <?php echo couleur_nation($clan_last_inscrit, $pseudo_last_inscrit); ?></b></td>
									</tr>
									<tr>
										<td><b>Persos actifs : <?php echo $nb_persos_actifs; ?> -- <font color='blue'>nordistes : <?php echo $nb_persos_nord_actifs; ?></font> / <font color='red'>sudistes : <?php echo $nb_persos_sud_actifs; ?></font></b></td>
									</tr>
								</table>
							</div>
							
							<br />
							<?php
							// récupération des news
							$sql_news = "SELECT date, contenu FROM news ORDER BY date DESC LIMIT 10";
							$res_news = $mysqli->query($sql_news);
							?>
							
							<div class='d-none d-md-block d-lg-block d-xl-block'>
								<table border='1' width='100%'>
									<tr>
										<th bgcolor="#FFFACD" style="text-align: center;">L'encre est encore fraiche</th>
									</tr>
									<tr>
										<td>
											<marquee onMouseOver=this.stop() onMouseOut=this.start() scrollAmount='2'  direction='up'>
											<?php
											while ($t_news = $res_news->fetch_assoc()){
								
												$date_news 		= $t_news["date"];
												$contenu_news 	= br2nl4(stripslashes($t_news["contenu"]));
												
												$date_news = new DateTime($date_news);
												
												echo "<br />";
												echo "<b>- <u>" . $date_news->format('d-m-Y') . "</u> : </b><br />";
												echo "<b>".nl2br($contenu_news)."</b>";
												echo "<br />";
											}
											?>
											</marquee>
										</td>
									</tr>
								</table>
							</div>
							
							<br />
							
							<div class='d-none d-md-block d-lg-block d-xl-block'>
								<table border='1' width='100%'>
									<tr>
										<th bgcolor="#FFFACD" style="text-align: center;">Présentation de Nord VS Sud</th>
									</tr>
									<tr>
										<td>
											<b>
											Bienvenue dans la lutte qui oppose le <font color='blue'>Nord</font> et le <font color='red'>Sud</font>.<br />
											Nous sommes à la fin du 19ème siècle et depuis des années, ces 2 armées se battent sous le commandement de leurs généraux respectifs : <font color='blue'>Abraham Lincoln</font> et <font color='red'>Jefferson Davis</font>.<br />
											Venez rejoindre l'un de ces camps pour soutenir ses efforts.<br />
											Vous commencerez en tant que Caporal et vous aurez sous vos ordres votre 1er grouillot.<br /><br />
											Au fur et à mesure de vos actions, votre reconnaissance et votre capacité à commander se révéleront. Votre montée en grade vous permettra d'avoir encore plus de grouillots sous vos ordres (4 types de grouillots : fantassin, cavalier, artilleur et soigneur).<br />
											Mais pour cela, il vous faudra utiliser tous les moyens disponibles : Relief du terrain, protection des bâtiments, achats d'armes et d'objets ainsi que le train à vapeur pour survivre au milieu du camp adverse et des bêtes sauvages.<br /><br />
											Alors, quel camp allez-vous faire gagner ?
											</b>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</body>
</html>
