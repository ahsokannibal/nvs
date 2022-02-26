<?php
$title = "Erreur 404";

/* --- Header --- */
ob_start();
?>
<div class="row justify-content-center text-center">
	<div class="col col-md-6">
		<h2>Vous êtes perdu ?</h2>
	</div>
</div>
<?php $header = ob_get_clean();

/* ---Content--- */
ob_start();
?>
<div class="row text-center">
	<div class="col-12 mb-4">
		<p>
			Il n'y a malheureusement rien ici.<br/>
			Retournez dans les rangs, votre patrie vous attend.
		</p>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>