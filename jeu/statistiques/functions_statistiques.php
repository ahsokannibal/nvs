<?php

require_once("../../fonctions.php");
if(isset($_POST['function']) && isset($_POST['type'])){
    if(strcmp($_POST["type"], "player") == 0){

        $json_data = file_get_contents('statistiques_sql.json');
        $sqlPropertiesObj = json_decode($json_data);

        switch($_POST['function']){
            case 'listAll':{
                header('Content-Type: application/json');
                echo json_encode(exec_sql(getJsonProperty($sqlPropertiesObj, 'listAllPlayers')));
            }break;
            case 'playersSideCharts' :{
                if(paramsIsSet()){
                    $params = json_decode($_POST['params'], true);//true to return an array
                    header('Content-Type: application/json');
                    echo json_encode(exec_sql_with_max_days(getJsonProperty($sqlPropertiesObj, 'listAllPlayersSideCharts'), $params['activeFor']));
                }
                
            }break;
            case 'playersGrouillotsCharts' :{
                if(paramsIsSet()){
                    $params = json_decode($_POST['params'], true);//true to return an array
                    header('Content-Type: application/json');
                    echo json_encode(exec_sql_with_max_days(getJsonProperty($sqlPropertiesObj, 'listAllPlayersGrouillotCharts'), $params['activeFor']));
                }
                
            }break;
            case 'playersGradeCharts' :{
                if(paramsIsSet()){
                    $params = json_decode($_POST['params'], true);//true to return an array
                    header('Content-Type: application/json');
                    echo json_encode(exec_sql_with_max_days(getJsonProperty($sqlPropertiesObj, 'listAllPlayersGradeCharts'), $params['activeFor']));
                }
                
            }break;
            
            case 'pgPieChart':{
                if(paramsIsSet()){
                    $params = json_decode($_POST['params'], true);//true to return an array
                    header('Content-Type: application/json');
                    echo json_encode(exec_sql_with_max_days(getJsonProperty($sqlPropertiesObj, 'listAllPgCharts'), $params['activeFor']));
                }
            }break;
        }
    }else if(strcmp($_POST["type"], "arme") == 0){

        $json_data = file_get_contents('statistiques_sql.json');
        $sqlPropertiesObj = json_decode($json_data);

        switch($_POST['function']){
            case 'listAll':{
                header('Content-Type: application/json');
                echo json_encode(exec_sql(getJsonProperty($sqlPropertiesObj, 'listAllArmes')));
            }break;
        }
    }
}

//Fonction qui vérifie qu'un paramètre a bien été reçu
function paramsIsSet(){
    if(isset($_POST['params'])){
        return true;
    }else{
        throw new Exception('No parameter received');
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

//Pour les requetes qui concernent les joueurs actif depuis $params jours
function exec_sql_with_max_days($sql, $active_for){
    $mysqli = db_connexion();
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $active_for);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_all(MYSQLI_ASSOC);
}

//Pour les requêtes qui ne nécessitent pas de paramètres
function exec_sql($sql){
    $mysqli = db_connexion();
    $sql = $sql;
    $res = $mysqli->query($sql);
    return $res->fetch_all(MYSQLI_ASSOC);
}