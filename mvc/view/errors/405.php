<?php
$title = "erreur 405";

/* ---Content--- */
ob_start();
?>
<div class="row justify-content-center">
	<div class="col-12 text-center mb-4">
		<h3 class='mb-4'>
			<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
			  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
			</svg>
			Erreur 405
		</h3>
		<h4>
			Méthode de requête non autorisée.
		</h4>
		<p>
			Retournez dans les rangs. Il n'y a rien à voir ici.
		</p>
		<a href='/'>
			<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 align-bottom me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
			  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
			</svg>
			Retour à l'accueil
		</a>
	</div>
</div>
		
<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/errors.php'); ?>