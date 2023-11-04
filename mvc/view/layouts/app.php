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
			<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

			<link rel="stylesheet" href="../public/css/app.css">

        <!-- Scripts -->
		<!-- Bunddle Popper.js & Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous" defer></script>
		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
		<script type="text/javascript" src="../public/js/app.js" defer></script>
    </head>
    <body class='game'>
        <div class='app'>
            <?php //include('layouts.navigation') ?>

            <!-- Page Header -->
            <header class='container-xxl my-5'>
				<?= $header ?>
            </header>

            <!-- Page Content -->
            <main class='container-xxl'>
				<?= $content ?>
            </main>
			
			<!-- Page Footer -->
			<footer class='container-xxl text-center my-3'>
				Nord vs Sud - Le jeu au tour par tour sur la guerre civile américaine</br>
				<a href ='../CGU.pdf'>Conditions générales d'utilisation</a> - <a href ='../CUDP.pdf'>charte d'utilisation des données personnelles</a>
			</footer>
        </div>
    </body>
</html>
<?php
	unset($_SESSION["flash"]);
	unset($_SESSION["old_input"]);
	unset($_SESSION["errors"]);
?>