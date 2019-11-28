<?php
define ("DUREE_TOUR", 24*60*60);//12*60*60);

//%%%%%%%%%%%%%%%%%%//
// FONCTION DE DB
//%%%%%%%%%%%%%%%%%%//
function db_connexion() //CONNEXION A LA DB
{
	$connexion = mysqli_connect("localhost","root","","nvs");
	if (!$connexion) {echo "Désolé, connexion à la bdd impossible"; exit(); }
	
	return $connexion;
}


//%%%%%%%%%%%%%%%%%%%%%%%%%//
// FONCTIONS DIVERSES
//%%%%%%%%%%%%%%%%%%%%%%%%%//

function br2nl($text) //fontion inverse de nlr2br (pour afficher mon texte dans les formulaires
{
	$text = str_replace("&lt;","<",$text);
	$text = str_replace("&gt;",">",$text);
	return $text;
	//return str_replace("&lt;br /&gt;", "<br />", $text);
}

function br2nl2($text) //fontion inverse de nlr2br (pour afficher mon texte dans les formulaires
{
	return str_replace("&lt;br /&gt;", "", $text);
}

function br2nl3($text) //fontion inverse de nlr2br (pour afficher mon texte dans les formulaires
{
	return str_replace("<br />", "", $text);
}

function br2nl4 ($text) {
	$text = str_replace("&lt;","<",$text);
	$text = str_replace("&gt;",">",$text);
	$text = str_replace("&quot;","",$text);
	return str_replace("<br />", "", $text);
}

function get_remote_address()
{
	return $_SERVER['REMOTE_ADDR'];
}

//fonction de mise en forme de texte (avec smileys)
function bbcode($text)
{
   $text = nl2br(addslashes($text));
   
   // gras
   $text = str_replace("[b]", "<b>", $text);
   $text = str_replace("[/b]", "</b>", $text);

   // souligne
   $text = str_replace("[u]", "<u>", $text);
   $text = str_replace("[/u]", "</u>", $text);

   // italique
   $text = str_replace("[i]", "<i>", $text);
   $text = str_replace("[/i]", "</i>", $text);
   
   // centrer
   $text = str_replace("[center]", "<center>", $text);
   $text = str_replace("[/center]", "</center>", $text);
   
   // couleur
   $text = preg_replace("#\[color=(.+?)\](.+?)\[\/color\]#si", "<font color=$1>$2</font>", $text);
   
   //table
   $text = str_replace("[table]", "<table border=1>", $text);
   $text = str_replace("[table noBorder]", "<table border=0>", $text);
   $text = str_replace("[tr]", "<tr>", $text);
   $text = str_replace("[td]", "<td>", $text);
   $text = str_replace("[/table]", "</table>", $text);
   $text = str_replace("[/tr]", "</tr>", $text);
   $text = str_replace("[/td]", "</td>", $text);
   
   // text align
   $text = preg_replace("#\[texte align ([a-z]+)\]#si", "<div style=\"text-align: \\1;\">", $text);
   $text = str_replace("[/texte]", "</div>", $text);
   
   // taille texte
   $text = preg_replace("#\[taille=([0-9])\]#si", "<font size= \\1;\">", $text);
   $text = str_replace("[/taille]", "</font>", $text);
   
   // titre
   $text = preg_replace("#\[titre C ([a-z]+) ([a-z]+)\]#si", "<div align=center style=\"background-color: \\1; color: \\2;\">", $text);
   $text = str_replace("[/titre]", "</div>", $text);
   
   // span grosses lettres   
   $text = str_replace("[span lettre]", "<span style=\"font-size: 200%; float: left;\">", $text);
   $text = str_replace("[/span]", "</span>", $text);
   
   // espaces
   $text = str_replace("  ", "&nbsp;&nbsp;", $text);
   
   // smilley
   $text = str_replace(':)','<img src="http://www.mapping-area.com/images/smiley/smile.gif" alt="" style="border:0"/>',$text);
   $text = str_replace(';)','<img src="http://www.mapping-area.com/images/smiley/hehe.gif" alt="" style="border:0"/>',$text);
   $text = str_replace(':P','<img src="http://www.mapping-area.com/images/smiley/langue.gif" alt="" style="border:0"/>',$text);
   $text = str_replace(':D','<img src="http://www.mapping-area.com/images/smiley/bigrin.gif" alt="" style="border:0"/>',$text);
   $text = str_replace(':o','<img src="http://www.mapping-area.com/images/smiley/bouh.gif" alt="" style="border:0"/>',$text);
   $text = str_replace('lol','<img src="http://loka.forumactif.com/images/smiles/lol!.gif" alt="" style="border:0"/>',$text);
   $text = str_replace('boulet!','<img src="http://loka.forumactif.com/images/smiles/boulet.gif" alt="" style="border:0"/>',$text);
   $text = str_replace(':\'(','<img src="http://loka.forumactif.com/images/smiles/icon_sad.gif" alt="" style="border:0"/>',$text);
   $text = str_replace(':evil:','<img src="http://sitebas.free.fr/forum/images/smiles/icon_evil.gif" alt="" style="border:0"/>',$text);

   $patterns = array();
   $replacements = array();

   // images
   $patterns[] = "#\[img\](.*?)\[/img\]#si";
   $replacements[] = "<img src=\"\\1\" border=\"0\" />";

   // url
   $patterns[] = "#\[url\]([a-z0-9]+?://){1}([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)\[/url\]#is";
   $replacements[] = "<a href=\"\1\2\" target=\"_blank\" class=\"postlink\">\1\2</a>";

   $patterns[] = "#\[url\]((www|ftp)\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*?)?)\[/url\]#si";
   $replacements[] = "<a href=\"http://\\1\" target=\"_blank\" class=\"postlink\">\\1</a>";

   $patterns[] = "#\[url=([a-z0-9]+://)([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*?)?)\](.*?)\[/url\]#si";
   $replacements[] = "<a href=\"\\1\\2\">\\6</a>";

   $patterns[] = "#\[url=(([\w\-]+\.)*?[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)\](.*?)\[/url\]#si";
   $replacements[] = "<a href=\"http://\\1\">\\5</a>";

   // email
   $patterns[] = "#\[email\]([a-z0-9\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#si";
   $replacements[] = "<a href=\"mailto:\\1\">\\1</A>";
   
   return preg_replace($patterns, $replacements, stripslashes($text));
}

function get_date($timestamp) {
	$monthes = array('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet',
								'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
	$days = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
	$date_first = $days[date('w', $timestamp)];
	$date_second = $monthes[date('n', $timestamp)];
	echo $date_first.' '.date('d', $timestamp).' '.$date_second.' '.date('Y', $timestamp).' à '.date('H:i:s', $timestamp); 
}
  
//**********************************//
// FONCTIONS DE FILTRE CHAINE VALIDE
//**********************************//
function filtre($chaine,$lenghtMin,$lenghtMax)
{
	$lenghtBefore = strlen($chaine);
	
	$caracteres = array(
	"," => "error",
	";" => "error",
	":" => "error",
	" " => "error",
	"!" => "error",
	"@" => "error",
	"|" => "error",
	"=" => "error",
	"+" => "error",
	"/" => "error",
	"*" => "error",
	"#" => "error",
	"'" => "error",
	"&" => "error",
	//" " => "error",
	);
	$chaine = strtr($chaine,$caracteres);	
	$chaine = trim(htmlentities($chaine) );
	$lenghtAfter = strlen($chaine);
	
	//echo $lenghtAfter;
	//echo " et ".$lenghtBefore;
	
	return !(($lenghtBefore < $lenghtAfter) || ($lenghtAfter < $lenghtMin) || ($lenghtBefore > $lenghtMax));
}

//**************************//
// FONCTIONS DE FILTRE MAIL
//**************************//
function filtremail($chaine)
{
	return( strpos($chaine,"@") > 0 && strrpos($chaine,".") > strpos($chaine,"@") );
}

//************************************//
// FONCTIONS AFFICHAGE DANS UN TABLEAU
//************************************//
function afficheResultat($resultat)
{
	if (mysql_num_rows($resultat) == 0) 
	{
		echo "<B><FONT color='black'>Aucun résultat trouvé</B></FONT>"; 
		exit();
	}
	$cols = 4;
	echo "<div align=\"center\"><TABLE BORDER=1>";
	$nbAttr = mysql_num_fields($resultat);
	$rows = $nbAttr / $cols;

	echo "<TR>";
	for ($i=0; $i < $nbAttr; $i++) 
		echo "<TH>" . mysql_field_name($resultat, $i) . "</TH>\n";
	echo "</TR>";

	while($tabAttr = mysql_fetch_row($resultat))
	{
		echo "<TR>";
		for($i=0; $i<$nbAttr; $i++) 
			echo "<TD>" . $tabAttr[$i] . "</TD>";
		echo "</TR>";
	}
	echo "</TABLE></div>";
}

//************************************//
// FONCTIONS DE MISE EN COULEUR NOM
//************************************//

function couleur_nation($nb, $nom) {
	if ($nb == 1)
		$color = "#FF0000"; //rouge
	elseif ($nb == 2)
		$color = "darkgreen"; // vert
	elseif ($nb == 3)
		$color = "#0000FF"; //bleu
	else
		$color = "#CCCCCC";	
	return "<b><font color=\"$color\">$nom</font></b>";
}

//************************************//
// FONCTIONS DE CALCUL DU TEMP DE JEU
//************************************//
function temps_2_jeu ($date) {
	$date=time()-strtotime($date);
	$temps[0]=floor($date/86400);
	$temps[1]=floor(($date-$temps[0]*86400)/3600);
	return($temps);
}

//***************************************//
// FONCTIONS DE POSITIONNEMENT DU PERSO
//***************************************//
function pos_perso_rand_x (){ // position x
	srand((double) microtime() * 1000000);
	$x = rand(0,200);
	return $x;
}

function pos_perso_rand_y (){ // position y
	srand((double) microtime() * 1000000);
	$y = rand(0,200);
	return $y;
}

//renvoie 1 si la case est occupée, 0 sinon
function verif_pos_libre($mysqli, $x, $y){ 
	$sql = "SELECT occupee_carte FROM carte WHERE x_carte='".$x."' AND y_carte='".$y."'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_row();
	$oc = $t[0];
	return $oc;
}

// position x
function pos_zone_rand_x($xMin,$xMax){ 
	srand((double) microtime() * 1000000);
	$x = rand($xMin,$xMax);
	return $x;
}

// position y
function pos_zone_rand_y($yMin,$yMax){ 
	srand((double) microtime() * 1000000);
	$y = rand($yMin,$yMax);
	return $y;
}

//***************************************//
// FONCTIONS DE CREATION DE JAUGE
//***************************************//

function get_color_pv ($pv, $pv_max){ 
	$pourc = $pv * 100 / $pv_max;
	if ($pourc < 25)
		$color = "#FF0000"; //rouge
	elseif ($pourc < 50)
		$color = "#ff9900"; //orange
	elseif ($pourc < 75)
		$color = "#FFFF00"; //jaune
	else 
		$color = "#00FF00"; //vert
	echo "<font color=\"$color\">$pv</font>";
}

function affiche_jauge ($p, $p_max){ 
	$pourc = $p * 100 / $p_max;
	if ($pourc < 25)
		$color = "#FF0000"; //rouge
	elseif ($pourc < 50)
		$color = "#ff9900"; //orange
	elseif ($pourc < 75)
		$color = "#FFFF00"; //jaune
	else 
		$color = "#00FF00"; //vert
	echo '<table border=1 width=202 height=8 cellpadding=1 cellspacing=0 bgcolor=0 bordercolor="black">';
		echo '<tr><td>';
				echo '<table align="center" width=100% height=6 border=0 cellpadding=0 cellspacing=0>';
					if ($pourc > 0)
						echo "<tr><td width=\"$pourc%\" height=6 bgcolor=\"$color\"><font size=1>&nbsp;</font></td>";
						echo "<td width=\"100-$pourc%\"bgcolor='#ffffff' height=6></td>";
					echo "</tr>";
				echo "</table>";
		echo "</td></tr>";
	echo "</table>";
	return $pourc;
}

function affiche_jauge_inverse ($p, $p_max){ 
	$pourc = $p * 100 / $p_max;
	if ($pourc < 25)
		$color = "#00FF00"; //vert
	elseif ($pourc < 50)
		$color = "#ffff00"; //jaune
	elseif ($pourc < 75)
		$color = "#ff9900"; //orange
	else 
		$color = "#FF0000"; //rouge
	echo '<table border=1 width=202 height=8 cellpadding=1 cellspacing=0 bgcolor=0 bordercolor="black">';
		echo '<tr><td>';
				echo '<table align="center" width=100% height=6 border=0 cellpadding=0 cellspacing=0>';
					if ($pourc > 0)
						echo "<tr><td width=\"$pourc%\" height=6 bgcolor=\"$color\"><font size=1>&nbsp;</font></td>";
						echo "<td width=\"100-$pourc%\"bgcolor='#ffffff' height=6></td>";
					echo "</tr>";
				echo "</table>";
		echo "</td></tr>";
	echo "</table>";
	return $pourc;
}
// autres

// Fonction qui vérifie s'il reste des pv
function reste_pv($pv) {
	return $pv <= 0;
}

// Fonction qui vérifie si il y a nouveau tour
function nouveau_tour($date, $dla) {
	return $date >= $dla;
} 

// Fonction qui récupére la nouvelle DLA
function get_new_dla($date, $dla) {
	$ecart = $date - $dla;
	$nb_tour = intval($ecart / DUREE_TOUR);
	return $dla + $nb_tour * DUREE_TOUR;
}

// Fonction qui vérifie si le temps passé aprés l'activation du gel est essez grand pour accepter le degel
function temp_degele($date, $date_gele){
	return $date - $date_gele >= 3*60*60*24; // 3 jours
}

// fonction qui retourne le temps restant avant le degele possible du perso
function temp_restant($date, $date_gele){
	$temp_gele_min = 3*60*60*24;
	return $temp_gele_min - ($date - $date_gele); 
}

// Fonction qui récupére l'adresse IP de l'utilisateur
function realip() {
   if (isSet($_SERVER)) {
    if (isSet($_SERVER["HTTP_X_FORWARDED_FOR"])) {
     $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (isSet($_SERVER["HTTP_CLIENT_IP"])) {
     $realip = $_SERVER["HTTP_CLIENT_IP"];
    } else {
     $realip = $_SERVER["REMOTE_ADDR"];
    }

   } else {
    if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
     $realip = getenv( 'HTTP_X_FORWARDED_FOR' );
    } elseif ( getenv( 'HTTP_CLIENT_IP' ) ) {
     $realip = getenv( 'HTTP_CLIENT_IP' );
    } else {
     $realip = getenv( 'REMOTE_ADDR' );
    }
   }
   return $realip;
}

/**
  * Fonction qui vérifie si un perso est admin
  * @param $id_perso	: l'identifiant du perso
  * @return Bool		: Si oui ou non le perso est admin
  */
function admin_perso($mysqli, $id_perso){
	$sql = "SELECT admin_perso FROM joueur, perso WHERE id_perso='$id_perso' AND perso.idJoueur_perso  = joueur.id_joueur";
	$res = $mysqli->query($sql);
	$t_admin = $res->fetch_assoc();
	return $t_admin["admin_perso"];
}

/**
  * Fonction qui retourne la configuration de disponibilité du jeu
  * @return Bool	: Si oui ou non le jeu est disponible
  */
function config_dispo_jeu($mysqli){
	$sql = "SELECT disponible FROM config_jeu";
	$res = $mysqli->query($sql);
	$t_dispo = $res->fetch_assoc();
	return $t_dispo["disponible"];
}

/**
  * Fonction qui envoi un mail au perso qui est gelé pour inactivité
  * @param $id_perso		: identifiant du perso inactif
  * @ return void
  */
function mail_gele_zombie($id_perso){
	
	// Recupération du mail de la cible
	$sql = "SELECT email_joueur, nom_perso FROM joueur, perso WHERE id_perso='$id_perso' AND id_joueur=idJoueur_perso";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();

	// Headers mail
	$headers ='From: "Nord VS Sud"<nordvssud@no-reply.fr>'."\n";
	$headers .='Reply-To: nordvssud@no-reply.fr'."\n";
	$headers .='Content-Type: text/plain; charset="iso-8859-1"'."\n";
	$headers .='Content-Transfer-Encoding: 8bit';
	
	// Destinataire du mail
	$destinataire = $t['email_joueur'];
	$nom_perso = $t['nom_perso'];
	
	// Titre du mail
	$titre = 'Géle de votre perso pour inactivité';
	
	// Contenu du mail
	$message = "Votre personnage $nom_perso a été placé en géle et retiré de la carte pour son inactivité. Si votre perso ne reprend pas d'activité d'ici 90 jours, il sera définitivement supprimé.";
	
	// Envoie du mail
	mail($destinataire, $titre, $message, $headers);
}

?>