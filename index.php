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
				<div class="col-12">
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
							<a href="inscription.php" style="color: white;">S'inscrire</b></a>
							<hr />
							<form action="login.php" method="post" name="pseudo" id="pseudo">
								<input name="pseudo" type="text" id="pseudo" value="pseudo" onClick="this.value=''" maxlength="30">
								<input name="password" type="password" id="password" value="password" onClick="this.value=''" maxlength="20">
								<input type="submit" name="Submit" value="Se connecter">
							</form>
							
							<a href="mdp_perdu.php" style="color: white;">Mot de passe perdu ?</b></a>
							<hr />
							<a href="faq.php" style="color: white;">FAQ - Régles</b></a>
							<hr />
							<a href="forum/forum.php" style="color: white;">Le Forum</b></a>
						</div>
						
						<div class="col-8">
						
							<div class='d-none d-md-block d-lg-block d-xl-block'>
								<table border='1' width='100%'>
									<tr>
										<th bgcolor="#FFFACD" style="text-align: center;">Quelques informations</th>
									</tr>
									<tr>
										<td><b>Nombre de joueurs inscrit : <br />Nombre de joueurs connectés :<br />Dernier inscrit :</b></td>
									</tr>
									<tr>
										<td><b>Persos actifs : <font color='blue'>nordistes : </font> / <font color='red'>sudistes : </font></b></td>
									</tr>
								</table>
							</div>
							
							<br />
							
							<div class='d-none d-md-block d-lg-block d-xl-block'>
								<table border='1' width='100%'>
									<tr>
										<th bgcolor="#FFFACD" style="text-align: center;">L'encre est encore fraiche</th>
									</tr>
									<tr>
										<td><b>Lancement de la version Alpha !</b></td>
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
