<?php

require_once("../../fonctions.php");
if(isset($_POST['function'])){
    switch($_POST['function']){
        case 'listAll':{
            header('Content-Type: application/json');
            echo json_encode(listAllPlayers());
        }break;
        case 'playersSideCharts' :{
            if(paramsIsSet()){
                header('Content-Type: application/json');
                echo json_encode(listAllPlayersSideCharts($_POST['params']));
            }
            
        }break;
        case 'playersGrouillotsCharts' :{
            if(paramsIsSet()){
                header('Content-Type: application/json');
                echo json_encode(listAllPlayersGrouillotCharts($_POST['params']));
            }
            
        }break;
        case 'playersGradeCharts' :{
            if(paramsIsSet()){
                header('Content-Type: application/json');
                echo json_encode(listAllPlayersGradeCharts($_POST['params']));
            }
            
        }break;
        
        case 'pgPieChart':{
            if(paramsIsSet()){
                header('Content-Type: application/json');
                echo json_encode(listAllPgCharts($_POST['params']));
            }
        }break;
    }
}

function paramsIsSet(){
    if(isset($_POST['params'])){
        return true;
    }else{
        echo 'No parameter received';
    }
}

function listAllPlayers(){
    $mysqli = db_connexion();
    $sql = "SELECT p.nom_perso as nom, p.id_perso as matricule, p.clan as camp, p.type_perso as 'type', p.bataillon as bataillon, g.nom_grade as grade FROM perso p LEFT JOIN perso_as_grade pag ON pag.id_perso = p.id_perso LEFT JOIN grades g ON g.id_grade = pag.id_grade";
    $res = $mysqli->query($sql);
    return $res->fetch_all(MYSQLI_ASSOC);
}

function listAllPlayersSideCharts($params){

}

function listAllPlayersGrouillotCharts($params){

}

function listAllPlayersGradeCharts($params){

}

function listAllPgCharts($params){

}