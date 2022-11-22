<?php
session_start();
require_once("../../fonctions.php");

$sqlPropertiesObj;

if(isset($_SESSION["id_perso"])){
    
    $perso = get_perso($_SESSION["id_perso"]);
    
    
}else{
//gerer non connecte    
   // exit();
   /* $joueur->id 		= 2;
	$joueur->clan 		= 2;
    $joueur->compagnie 	= 'TIG-RES';
    $joueur->bataillon  = 'Général du Sud';*/
}

function get_perso($id_perso){
	
    $mysqli = db_connexion();
	// recuperation de l'id et du clan du chef
	$sql = "SELECT p.id_perso, p.clan, p.bataillon, p.idJoueur_perso, comp.nom_compagnie FROM perso p LEFT JOIN perso_in_compagnie pic ON pic.id_perso = p.id_perso LEFT JOIN compagnies comp ON comp.id_compagnie = pic.id_compagnie WHERE p.id_perso=$id_perso";
	$res = $mysqli->query($sql);
	$t_chef = $res->fetch_assoc();
	
    $perso = new StdClass();
	$perso->id 		    = $t_chef["id_perso"];
	$perso->id_joueur 	= $t_chef["idJoueur_perso"];
	$perso->clan 		= $t_chef["clan"];
	$perso->compagnie 	= $t_chef["nom_compagnie"];
    $perso->bataillon   = $t_chef["bataillon"];
    return $perso;
}

if(isset($_POST['function'])){

    $json_data = file_get_contents('carte_sql.json');
    $sqlPropertiesObj = json_decode($json_data);

    switch($_POST['function']){
        case 'get_map':{
            header('Content-Type: application/json');
            if($perso->clan == 0){
                echo  json_encode(array());
                exit();
            }
            $json_map = get_json_map($sqlPropertiesObj, $perso, false);
            echo $json_map;
            break;
        }
        case 'do_historique':{
            //historique de carte des généraux sans les informations sur les compagnies et bataillon
            $lincoln = get_perso(1);
            $json_map = get_json_map($sqlPropertiesObj, $lincoln, true);
            save_historique_map($sqlPropertiesObj, $json_map, $lincoln);
            
            $davis = get_perso(2);
            $json_map = get_json_map($sqlPropertiesObj, $davis, true);
            save_historique_map($sqlPropertiesObj, $json_map, $davis);
            break;
        }
        case 'get_historique':{
            if(isset($_POST['date'])){
                header('Content-Type: application/json');
                if($perso->clan == 0){

                    echo  json_encode(array());
                    exit();
                }
                //$davis = get_perso(2); //uncomment to test
                $json_map = get_json_historique_map($sqlPropertiesObj, $perso, $_POST['date']);
                echo $json_map;
            }else{
                echo'Error no date provided';
            }
            break;
        }
    }
}

//Fonction qui récupère le sql du fichier json
function getJsonProperty($json, $property){
    if(isset($json->$property)){
        return $json->$property;
    }else{
        throw new Exception("Property : " . $property . " does not exist");
    }
}

//fonction qui créé le json de la map
function get_json_map($sqlPropertiesObj, $joueur, $isHistorique){
    $sql_clan = get_sql_clan($joueur, getJsonProperty($sqlPropertiesObj, 'cases_deja_vues'));
    $carte_array = array();
    $cases_deja_vues = get_cases_deja_vues($sql_clan);
    
    foreach ($cases_deja_vues as $case) {
        
        $carte_array[$case['id']]=array(
            'x'     =>  $case["x_carte"],
            'y'     =>  $case["y_carte"],
            'fond'  =>  $case["fond_carte"]
        );
    }
    
    
    $sql_clan = get_sql_clan($joueur, getJsonProperty($sqlPropertiesObj, 'brouillard'));

    //On récupère toutes les cases brouillard
    $brouillard = get_brouillard($sql_clan, $joueur->clan);
    foreach ($brouillard as $case) {
        $carte_array[$case['id']]['brouillard']=array(
            'valeur'=>  '1'
        );
    }
    
    
    $sql_clan = get_sql_clan($joueur, getJsonProperty($sqlPropertiesObj, 'visible'));
    $visible = get_visibles($sql_clan, $joueur->clan);
    foreach ($visible as $case) {
        //visible si le perso n'est pas en foret et qu'il n'est pas du même camp
        if ($case["idPerso_carte"] < 50000 && $case["idPerso_carte"] > 0){
            if(!isset($carte_array[$case['id']]['brouillard'])){
                $carte_array[$case['id']]['joueur']=array(
                    'id'        => $case["idPerso_carte"],
                    'image'     => $case["image_carte"],
                    'camp'      => $case["clan"]         
                );
                if(!$isHistorique && $case["idJoueur_perso"] == $joueur->id_joueur){
                    $carte_array[$case['id']]['joueur']['bataillon'] = trim($case["bataillon"]);
                }
                if(!$isHistorique && isSet($case["nom_compagnie"]) && $joueur->compagnie == $case["nom_compagnie"]){
                    $carte_array[$case['id']]['joueur']['compagnie'] = trim($case["nom_compagnie"]);
                }
            }/*else{
                $carte_array[$case['id']]['joueur']=array(
                    'camp'  =>  $case["clan"]
                );
            }*/
        }else if ($case["idPerso_carte"] >= 200000){
            $carte_array[$case['id']]['pnj']=array(
                'id'    =>  $case["idPerso_carte"],
                'image' =>  $case["image_carte"]
            );
        }else if ($case["idPerso_carte"] < 200000 && $case["idPerso_carte"] >= 50000){
            $image;
            if($case["nom_batiment"] == 'Pont'){
                $image = $case["fond_carte"];
            }else{
                $image = $case["image_carte"];
            }
            $carte_array[$case['id']]['batiment']=array(
                'id'        =>  $case["idPerso_carte"],
                'image'     =>  $image,
                'camp'      =>  $case["camp_instance"],
                'nom'       =>  $case["nom_batiment"]
            );
        }
    }

    $persos_in_batiment = get_persos_in_batiments(getJsonProperty($sqlPropertiesObj, 'persos_dans_batiments'), $joueur->clan);
    foreach ($persos_in_batiment as $case) {
        $case_joueur = array(
            'id'        => $case["id_perso"],
            'camp'      => $case["clan"]         
        );
        if(!$isHistorique && $case["idJoueur_perso"] == $joueur->id_joueur){
            $case_joueur["bataillon"]=trim($case["bataillon"]);
        }
        if(!$isHistorique && isSet($case["nom_compagnie"]) && $joueur->compagnie == $case["nom_compagnie"]){
            $case_joueur["compagnie"] = trim($case["nom_compagnie"]);
        }
        
        $carte_array[$case['id']]['joueur'][]=$case_joueur;
        
    }
    return json_encode($carte_array);
}

//fonction qui récupère une map historique à une date donnée
function get_json_historique_map($sqlPropertiesObj, $joueur, $dateHistorique){
    $sql = getJsonProperty($sqlPropertiesObj, 'get_carte_historique');
    $mysqli = db_connexion();
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param('is', $joueur->clan, $dateHistorique);
        $stmt->execute();
    }else{
        echo 'Error';
        die();
    }
    $res = $stmt->get_result();
    if($res->num_rows != 1){
        echo 'Error, not 1 result';
        die();
    }
    $row = $res->fetch_row();
    return $row[0];
}

//fonction qui sauvegarge un historique de la map
function save_historique_map($sqlPropertiesObj, $json_map, $chef_de_clan){
    if (!exist_today_carte_historique($sqlPropertiesObj, $chef_de_clan)){
        $sql = getJsonProperty($sqlPropertiesObj, 'insert_carte_historique');
        $mysqli = db_connexion();
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param('is', $chef_de_clan->clan, $json_map);
            $stmt->execute();
        }else{
            echo 'Error';
            die();
        }
        return 'Success';
    }else{
        echo 'entry already exists';
    }
}

//fonction qui teste si un historique de carte a deja été fait aujourd'hui
function exist_today_carte_historique($sqlPropertiesObj, $chef_de_clan){
    $sql = getJsonProperty($sqlPropertiesObj, 'select_today_carte_historique');
    $mysqli = db_connexion();
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param('i', $chef_de_clan->clan);
        $stmt->execute();
        
        $res = $stmt->get_result();
        return $res->num_rows > 0;
    }else{
        return false;
    }
}

//bout de sql à ajouter aux requetes en fonction du clan du joueur
function get_sql_clan($joueur, $sql){
    
    if ($joueur->clan == '2'){
        $sql = str_replace("vue_nord", "vue_sud", $sql);
    }else{
       // str_replace("vue_nord", "", $sql);
    }
    return $sql;
}

//Brouillard de guerre
function get_brouillard($sql, $camp){
    $mysqli = db_connexion();
    $stmt = $mysqli->prepare($sql);
    $brouillard_duration = BROUILLARD_DE_GUERRE_S;
    $stmt->bind_param('iiii', $camp, $camp, $camp, $brouillard_duration);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_all(MYSQLI_ASSOC);
    
}

//Visibles
function get_visibles($sql, $camp){
    $mysqli = db_connexion();
    $stmt = $mysqli->prepare($sql);
    $brouillard_duration = BROUILLARD_DE_GUERRE_S;
    $stmt->bind_param('iiii', $camp, $camp, $camp, $brouillard_duration);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_all(MYSQLI_ASSOC);
    
}

//case déjà vues
function get_cases_deja_vues($sql){
    $mysqli = db_connexion();
    $res = $mysqli->query($sql);
    return $res->fetch_all(MYSQLI_ASSOC);
    
}

//persos du meme camp dans les batiments
function get_persos_in_batiments($sql, $camp){
    $mysqli = db_connexion();
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $camp);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_all(MYSQLI_ASSOC);
    
}