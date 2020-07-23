<?php
require_once("../fonctions.php");

$mysqli = db_connexion();
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

	<body style="background-image:url('../images/background_html.jpg'); color:#FFFFFF">

		<div class="container-fluid">
			
			<div class="row justify-content-center">
				<div class="col-12">
	
					<div align="center"><h2>L'animation</h2></div>
					
				</div>
			</div>
			
			<div class="row justify-content-center">
				<div class="col-12" align="center">
				
<p><b><i><font color='red'>Cette page est très importante !!! Veuillez prendre connaissance de ce qui suit !!!</font></i></b></p><br />
					
				</div>
			</div>
			
			<br /><br/ ><br /><br />
			
			<div class="row">
				<div class="col-12">
					<h2>Liste des animateurs</h2>
					<h4>Animateurs du Nord</h4>
					<?php
					$sql = "SELECT nom_perso, id_perso FROM perso, joueur 
							WHERE perso.idJoueur_perso = joueur.id_joueur
							AND clan='1'
							AND chef='1'
							AND animateur='1'";
					$res = $mysqli->query($sql);
					while ($t = $res->fetch_assoc()) {
						
						$id_perso 	= $t['id_perso'];
						$nom_perso 	= $t['nom_perso'];
						
						echo "<b>- <font color='#2288FF'>".$nom_perso."</b></font> [".$id_perso."]<br />";
						
					}
					?>
					<br />
					<h4>Animateurs du Sud</h4>
					<?php
					$sql = "SELECT nom_perso, id_perso FROM perso, joueur 
							WHERE perso.idJoueur_perso = joueur.id_joueur
							AND clan='2'
							AND chef='1'
							AND animateur='1'";
					$res = $mysqli->query($sql);
					while ($t = $res->fetch_assoc()) {
						
						$id_perso 	= $t['id_perso'];
						$nom_perso 	= $t['nom_perso'];
						
						echo "<b>- <font color='#FF4444'>".$nom_perso."</b></font> [".$id_perso."]<br />";
						
					}
					?>
					<br />
					<h2>Rôles de l'animation</h2>
					<h4>Support de premier niveau</h4>
<p>Les animateurs ont un rôle de support de premier niveau et doivent être capable de rèpondre aux questions des joueurs, si besoin en utilisant la console d'animation à leur disposition pour trouver les informations qu'ils ont besoin (état d'un bâtiment, événements détaillé du perso, etc..) mais doit faire attention à le faire sans révéler d'informations stratégiques.</p>
<p></p>

					<h4>Gérer l'administratif de leur camp</h4>
<p>
Une compagnie souhaite changer de nom : c'est un animateur qui doit valider / refuser.<br />
Une compagnie souhaite être dissoute : c'est un animateur qui doit valider / refuser.<br />
Un joueur souhaite changer le nom de son chef : c'est un animateur qui doit valider / refuser.<br />
Un joueur souhaite changer le nom de son bataillon : c'est un animateur qui doit valider / refuser.<br />
Un joueur souhaite changer de camp : c'est un animateur qui doit valider / refuser.</p>
<p>Les validations / refus doivent se faire en bonne intelligence. L'animateur doit vérifier si les demandes sont bien motivées par du RP, par une erreur à l'inscription ou par un besoin utile. <br />
Toute demande non motivée se doit d'être refusée.</p>
<p>Il est amené à pouvoir nommer / renommer les bâtiments de son camp en étant en cohérence avec les besoins des joueurs de son camp.</p>
<p>Il doit aussi veiller au respect des règles et du fairplay par les joueurs de son camp et pourra si necessaire punir des persos en appliquant amendes et/ou envoi au pénitencier.</p>
					<h2>Règles de l'animation</h2>
<p>Un animateur se doit de rester neutre, il ne doit en <b>AUCUN CAS</b>, de par ses actions d'animateur, hors proposition de missions / récompenses RP, participer à faire gagner son camp.</p>
<p>Si cela devait arriver, l'animateur serait renvoyé, ses persos pourraient être supprimés et un malus de point de victoire pourrait être appliqué à son camp selon (selon le jugement)</p>
<p>De même, toute information découverte grâce à la console Anim ne doit en aucun cas être utilisée dans le jeu.<br />Par exemple, si l'animateur découvre qu'un bâtiment important de son camp se trouve en état de siège alors que ses PV sont au dessus de 90% (et donc que c'est qu'il y a des ennemis proches), il ne doit pas avertir les persos/EM de son camp que c'est le cas ou prendre des mesures pour contrer l'infiltration / l'attaque ennemie !</p>
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