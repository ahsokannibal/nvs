<?php

function est_couleur_plaine($r, $g, $b) {
	return $r == "81" 
			&& $g == "9c" 
			&& $b == "54";
}

function est_couleur_colline($r, $g, $b) {
	return $r == "60" 
			&& $g == "6e" 
			&& $b == "46";
}

function est_couleur_montagne($r, $g, $b) {
	return $r == "86" 
			&& $g == "76" 
			&& $b == "59";
}

function est_couleur_desert($r, $g, $b) {
	return $r == "d7" 
			&& $g == "c5" 
			&& $b == "65";
}

function est_couleur_marecage($r, $g, $b) {
	return $r == "a9" 
			&& $g == "b1" 
			&& $b == "a6";
}

function est_couleur_foret($r, $g, $b) {
	return $r == "3c" 
			&& $g == "56" 
			&& $b == "21";
}

function est_couleur_eau($r, $g, $b) {
	return $r == "86" 
			&& $g == "9b" 
			&& $b == "be";
}

function est_couleur_eau_profonde($r, $g, $b) {
	return $r == "27" 
			&& $g == "8d" 
			&& $b == "e3";
}

?>