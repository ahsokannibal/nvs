<?php
function ameliore_pa($nb){
	$resultat = ceil(((($nb+1) * ($nb+1)) + 2 * ($nb+1)) / 2);
	return $resultat;
}

function ameliore_pm($nb){
	$resultat = ceil((($nb+1) * ($nb+1)) / 2);
	return $resultat;
}

function ameliore_pv($nb){
	$resultat = ceil(($nb-39) / 2);
	return $resultat;
}

function ameliore_perc($nb){
	$resultat = ceil((($nb+1) * ($nb+1)) / 3);
	return $resultat;
}

function ameliore_recup($nb){
	$resultat = ceil((($nb+1) * ($nb+1)) / 4);
	return $resultat;
}

function ameliore_charge($nb){
	$resultat = ceil(($nb+1) * 2);
	return $resultat;
}
?>