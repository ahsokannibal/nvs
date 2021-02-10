<?php

function dump_mysql($mysqli, $serveur, $login, $password, $base, $mode) {
	
	$entete  = "-- ----------------------\n";
    $entete .= "-- dump de la base ".$base." au ".date("d-m-Y")."\n";
    $entete .= "-- ----------------------\n\n\n";
    $creations = "";
    $insertions = "\n\n";
	
	$sql = "show tables";
	$res = $mysqli->query($sql);
	
	while ($table = $res->fetch_array()) {
		
		$nom_table = $table[0];
		
		// structure ou la totalité de la BDD
        if($mode == 1 || $mode == 2) {			
			$creations .= "-- -----------------------------\n";
            $creations .= "-- Structure de la table ".$nom_table."\n";
            $creations .= "-- -----------------------------\n";
			
			$sql_t = "show create table ".$nom_table;
			$res_t = $mysqli->query($sql_t);
			
			while ($creationTable = $res_t->fetch_array()) {
				$creations .= $creationTable[1].";\n\n";
			}		
		}
		
		// données ou la totalité
        if($mode > 1) {
		
            $insertions .= "-- -----------------------------\n";
            $insertions .= "-- Contenu de la table ".$nom_table."\n";
            $insertions .= "-- -----------------------------\n";
			
			$sql_d = "SELECT * FROM ".$nom_table;
			$res_d = $mysqli->query($sql_d);
			$nb_fields = $res_d->field_count;
			
			while ($nuplet = $res_d->fetch_array()) {
				$insertions .= "INSERT INTO ".$nom_table." VALUES(";
				for($i=0; $i < $nb_fields; $i++) {
					if($i != 0) {
						$insertions .=  ", ";
					}
					
					if($res_d->fetch_field_direct($i) == "string" || $res_d->fetch_field_direct($i) == "blob") {
						$insertions .=  "'";
					}
					
					$insertions .= addslashes($nuplet[$i]);
					
					if($res_d->fetch_field_direct($i) == "string" || $res_d->fetch_field_direct($i) == "blob") {
						$insertions .=  "'";
					}
				}
				$insertions .=  ");\n";
			}
			$insertions .= "\n";
		}
	}
	
	$nom_fichier = "dump_nvs_".date("Y-m-d").".sql";
	
	$fichierDump = fopen($nom_fichier, "wb");
    fwrite($fichierDump, $entete);
    fwrite($fichierDump, $creations);
    fwrite($fichierDump, $insertions);
    fclose($fichierDump);
}

?>