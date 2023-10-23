<?php
require_once("Model.php");

class Administration extends Model
{
  /**
  * Get the statut of the Maintenance Mode
  * @return Bool
  */
	public function getMaintenanceMode()
    {
		try{
			$db = $this->dbConnectPDO();
			$query = "SELECT * FROM config_jeu WHERE code_config='disponible'";
		
			$request = $db->prepare($query);
			$request->execute();
			$result = $request->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch(Exception $e) {
			die('Erreur : '.$e->getMessage());
		}
    }

   /**
   * Update the statut of the Maintenance Mode
   * @return Bool
   */
	public function switchMaintenance($value)
    {
		try{
			$db = $this->dbConnectPDO();
			$query = "UPDATE config_jeu SET valeur_config=:value WHERE code_config='disponible'";
		
			$request = $db->prepare($query);
			$request->bindParam('value', $value, PDO::PARAM_BOOL);
			$result = $request->execute();

			return $result;
		}
		catch(Exception $e) {
			die('Erreur : '.$e->getMessage());
		}
    }

   /**
   * Update the message of the Maintenance Mode
   * @return Bool
   */
	public function updateMaintenanceMsg(string $value)
    {
		try{
			$db = $this->dbConnectPDO();
			$query = "UPDATE config_jeu SET msg=:value WHERE code_config='disponible'";
		
			$request = $db->prepare($query);
			$request->bindParam('value', $value, PDO::PARAM_STR);
			$result = $request->execute();

			return $result;
		}
		catch(Exception $e) {
			die('Erreur : '.$e->getMessage());
		}
    }
}