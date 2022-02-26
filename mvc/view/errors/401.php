<?php
$title = "Erreur 401";

/* --- Header --- */
ob_start();
?>
<div class="row justify-content-center text-center">
	<div class="col col-md-6">
		<h2>HALTE SOLDAT !</h2>
	</div>
</div>
<?php $header = ob_get_clean();

/* ---Content--- */
ob_start();
?>
<div class="row text-center">
	<div class="col-12 mb-4">
		<p>
			Vous n'êtes pas autorisé à entrer dans cette tente.<br/>
			Identifiez vous pour obtenir un laisser-passer.<br/>
			<a href='index.php'>Accueil</a>
		</p>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>