<?php
require_once("Model.php");

class Camp extends Model
{
	// protected $fillable = [];
	protected $guarded = [];

	/*
	 * récupérer les infos du camp 
	 *
	*/
	// Rétrocompatibilité
    public function getCamp($id){
		switch($id){
			case 1:
				$camp = [
					'id' => 1,
					'name' => 'Nord',
					'desc' => "Armée de l'union",
					'color' => "blue"
				];
				break;
			case 2:
				$camp = [
					'id' => 2,
					'name' => 'Sud',
					'desc' => "Armée des Etats confédérés",
					'color' => "red"
				];
				break;
			default:
				$camp = [
					'id' => 0,
					'name' => 'Sans patrie',
					'desc' => "Armée de mercenaire",
					'color' => "grey"
				];
			
		}
		return $camp;
	}
}