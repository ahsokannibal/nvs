<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Le jeu au tour par tour sur la guerre de sécession">
		<meta name="author" content="Maxime RAFFIN">

        <title><?php if($title){echo $title.' - ';}?>Nord vs Sud</title><!--1861 : Blood and War-->
		
		<!--<link rel="shortcut icon" href="public/favicon.ico">-->
		<!--<link rel="icon" type="image/png" href="public/favicon.png">-->

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
			<!-- Bootstrap CSS -->
			<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
			<link rel="stylesheet" href="public/css/app.css">

        <!-- Scripts -->
		<!-- Bunddle Popper.js & Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous" defer></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous" defer></script>
		<script type="text/javascript" src="public/js/app.js" defer></script>
    </head>
    <body>
        <div class='app'>
            <?php //include('layouts.navigation') ?>

            <!-- Page Header -->
            <header class='container-xxl my-0 mt-sm-3 mb-sm-5'>
				<div class="row text-center">
					<div class="col-4">
						<a href='index.php' class='text-decoration-none text-reset'>
							<img src="images/accueil/logo_NVS_lee.png" alt='logo NVS' class="img-fluid" />
						</a>
					</div>
					<div class="col-8">
							<img src="images/accueil/baniere_accueil.jpg" alt='bannière NVS' class="img-fluid" />
					</div>
						<!--
						<h1>1861 : Nord vs Sud</h1>
						<h4>jeu multijoueur au tour par tour</h4>
						-->
				</div>
            </header>

            <!-- Page Content -->
            <main class='container-xxl'>
				<?= $content ?>
            </main>
			
			<!-- Page Footer -->
			<footer class='container-xxl text-center my-3'>
				Nord vs Sud - Le jeu au tour par tour sur la guerre civile américaine
			</footer>
        </div>
    </body>
</html>