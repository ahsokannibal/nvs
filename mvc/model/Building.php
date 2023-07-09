<?php
require_once("Model.php");

class Building extends Model
{
   public function getByType(int $type,int $camp=null){
		$db = $this->dbConnectPDO();
		
		if($camp){
			$query = "SELECT id_instanceBat, id_batiment, nom_instance, x_instance, y_instance, contenance_instance FROM instance_batiment WHERE camp_instance=$camp AND id_batiment=$type ORDER BY nom_instance";
		}else{
			$query = "SELECT id_instanceBat, id_batiment, nom_instance, x_instance, y_instance, contenance_instance FROM instance_batiment WHERE id_batiment=$type ORDER BY nom_instance";
		}
		
		$request = $db->prepare($query);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_ASSOC);
		
		$result = $request->fetchAll();

		return $result;
   }
   
   public function getById(int $id){
		$db = $this->dbConnectPDO();
		
		$query = "SELECT id_instanceBat, id_batiment, nom_instance, x_instance, y_instance, contenance_instance FROM instance_batiment WHERE id_instanceBat=$id";
		
		$request = $db->prepare($query);
		$request->execute();
		
		$result = $request->fetch();

		return $result;
   }
}