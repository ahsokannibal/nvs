<?php
//session_start();
//Commme d'ab
require_once "../fonctions.php";

// $mysqli = db_connexion();
// $imageOutput = new PlanGareImage(1, $mysqli);
// $imageOutput->ShowImage();

class PlanGareImage{
	//SET VARIABLES
	private $camp = 0;
	private $sqli;

	// output variables
	private $gare_carte;
	private $image_p;
	private $image_background;


	//CONST
	private $imageBackground_path = "carte/carte.png";
	private $imageSize = array("width"=> 600, "height" =>600, "brushSize"=>3); //doivent etre multiples de 3
	//COLOR SPACE
	// couleurs perso_carte
	private $noir;
	private $couleur_nord ;
	private $couleur_sud;
	private $couleur_rail;

	function __construct($clan, $mysqli){
		$this->camp = $clan;
		$this->sqli = $mysqli;

		$this->gare_carte = imagecreate($this->imageSize["width"],$this->imageSize["height"])  or die ("Cannot Initialize new GD image stream");
		$alpha_channel = imagecolorallocatealpha($this->gare_carte, 0, 0, 0, 127);
        imagecolortransparent($this->gare_carte, $alpha_channel);
        // Fill image
        imagefill($this->gare_carte, 0, 0, $alpha_channel);

		$this->image_p = imagecreatetruecolor($this->imageSize["width"], $this->imageSize["height"]);
		$this->image_background = imagecreatefrompng($this->imageBackground_path) or die("Impossible d'acceder a l'image de fond");

		imagecopyresampled($this->image_p, $this->image_background , 0, 0, 0, 0, $this->imageSize["width"], $this->imageSize["height"], $this->imageSize["width"], $this->imageSize["height"]);

		$this->noir 			= Imagecolorallocate($this->gare_carte, 0, 0, 0); // noir
		$this->couleur_nord 	= Imagecolorallocate($this->gare_carte, 10, 10, 254); // bleu bien voyant
		$this->couleur_sud 		= Imagecolorallocate($this->gare_carte, 254, 10, 10); // rouge bien voyant
		$this->couleur_rail		= Imagecolorallocate($this->gare_carte, 200, 200, 200); // gris rails

		$this->Hide_Undiscovered();
		$this->Put_Rails();
		$this->Put_Gares();

	}

	function Hide_Undiscovered(){
		if($this->camp == 1){
			$sql = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE vue_nord='0'";

		}else if ($this->camp == 2){
			$sql = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE vue_sud='0'";

		}else{
			throw new Exception("Le camp n'est pas specifie ou a une valeur diffrente de 1 ou 2");
			die("Le camp est mal specifié, image de la carte inaccessible");
		}

		$res = $this->sqli->query($sql);
		while ($t = $res->fetch_assoc()){
			$x 			= $t["x_carte"];
			$y 			= $t["y_carte"];
			$fond		= $t["fond_carte"];

			// cacher les elements de carte non decouverts
Imagefilledrectangle ($this->gare_carte, (($x*$this->imageSize["brushSize"])-1), ((($this->imageSize["height"]-$this->imageSize["brushSize"]-($y*$this->imageSize["brushSize"])))-1), (($x*$this->imageSize["brushSize"])+1), ((($this->imageSize["height"]-$this->imageSize["brushSize"]-($y*$this->imageSize["brushSize"])))+1), $this->noir);
		}

	}

	function Put_Rails(){
		if($this->camp == 1 || $this->camp == 2){
			// Recently used rails
			$camp = $this->camp;
			$sql =	"SELECT DISTINCT train_last_dep.x_last_dep as x_carte, train_last_dep.y_last_dep as y_carte
					FROM instance_batiment
					inner join train_last_dep ON train_last_dep.id_train = instance_batiment.id_instanceBat
					WHERE instance_batiment.id_batiment = 12 and instance_batiment.camp_instance = $camp and train_last_dep.DeplacementDate > DATE_SUB(CURDATE(), INTERVAL 5 day)";

			$res = $this->sqli->query($sql);

			while ($t = $res->fetch_assoc()){

				$x = $t["x_carte"];
				$y = $t["y_carte"];

				imagefilledrectangle ($this->gare_carte, (($x*3)-1), ((($this->imageSize["height"]-($y*3)))-1), (($x*3)+1), ((($this->imageSize["height"]-($y*3)))+1), $this->couleur_rail);

			}
		}else{
			throw new Exception("Le camp n'est pas specifie ou a une valeur diffrente de 1 ou 2");
			die("Le camp est mal specifié, image de la carte inaccessible");
		}
	}

	function Put_Gares(){
		if($this->camp == 1 || $this->camp == 2){
			// je vais chercher les gares dans ma table
			$camp = $this->camp;
			$sql = "SELECT x_instance, y_instance, nom_instance, taille_batiment, camp_instance FROM instance_batiment, batiment
					WHERE batiment.id_batiment = instance_batiment.id_batiment
					AND pv_instance>0
					AND instance_batiment.id_batiment='11'
					AND camp_instance = $camp";
			$res = $this->sqli->query($sql);

			while ($t = $res->fetch_assoc()){

				$x 			= $t["x_instance"];
				$y 			= $t["y_instance"];
				$taille_bat = $t["taille_batiment"];
				$camp_bat	= $t["camp_instance"];
				$nom_bat	= "Gare ".$t["nom_instance"];

				$taille_text = strlen($nom_bat);

				if ($camp_bat == 1) {
					$color = $this->couleur_nord;
				}
				else {
					$color = $this->couleur_sud;
				}

				imagefilledrectangle ($this->gare_carte, (($x*3)-$taille_bat), ($this->imageSize["height"]-($y*3))-($taille_bat), (($x*3)+$taille_bat), ((($this->imageSize["height"]-($y*3)))+$taille_bat), $color);

				ImageString($this->gare_carte, 12, ($x*3)-($taille_text*3), (($this->imageSize["height"]-($y*3))) + 3, $nom_bat, $this->noir);
			}
		}else{
			throw new Exception("Le camp n'est pas specifie ou a une valeur diffrente de 1 ou 2");
			die("Le camp est mal specifié, image de la carte inaccessible");
		}
	}

	public function ShowImage($step = "final"){
		imagecopymerge($this->image_p, $this->gare_carte, 0, 0, 0, 0, $this->imageSize["width"], $this->imageSize["height"], 100);
		//header("Content-type: image/png");//on va commencer par declarer que l'on veut creer une image
		// on affiche l'image

		switch($step){
			case "final":
				imagepng($this->image_p);
				break;
			case "initial":
				imagepng($this->image_background);
				break;
			case "calque":
				imagepng($this->gare_carte);
				break;
		}
	}

	function GetImage($step = "final"){
		imagecopymerge($this->image_p, $this->gare_carte, 0, 0, 0, 0, $this->imageSize["width"], $this->imageSize["height"], 100);

		switch($step){
			case "final":
				return $this->image_p;

			case "initial":
				return $this->image_background;

			case "calque":
				return $this->gare_carte;

		}
	}

	function Clear(){
		ImageDestroy ($this->gare_carte);
		ImageDestroy ($this->image_carte);
		ImageDestroy ($this->image_background);
	}
}
?>
