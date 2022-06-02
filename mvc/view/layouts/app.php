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
			<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
			<link rel="stylesheet" href="../public/css/app.css">

        <!-- Scripts -->
		<!-- Bunddle Popper.js & Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous" defer></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
		<script type="text/javascript" src="../public/js/app.js" defer></script>
    </head>
    <body>
        <div class='app'>
			<?php if(isset($_SESSION['maintenance'])&&$_SESSION['maintenance']):?>
			<div class='alert alert-warning fw-bold text-center'>
				<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
				  <path stroke-linecap="round" stroke-linejoin="round" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016zM12 9v2m0 4h.01" />
				</svg>
				Mode Maintenance activé
			</div>
			<?php endif; ?>
            <?php include('nav.php') ?>

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
				Nord vs Sud - Le jeu au tour par tour sur la guerre civile américaine
			</footer>
        </div>
    </body>
</html>