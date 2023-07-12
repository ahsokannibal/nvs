<?php
require_once($_SERVER['DOCUMENT_ROOT']."/mvc/Db/Db.php");

abstract class Model extends Db
{
	
	protected $table;
	protected $primaryKey = 'id';
	protected $fillable = []; // seuls les champs de ce tableau seront autorisés dans une hydratation
	protected $guarded; //  tous les champs de ce tableau seront enlevés d'une hydratation
	
	private $modelAttr = ['table','primaryKey','fillable','guarded','modelAttr','db'];
	private $db;
	
	public function __construct()
	{
		// par défaut la table est le nom de la classe
		if(empty($this->table)){
			$this->table = get_class($this);
		}
	}
	
	protected function request(string $sql, array $attributs = null)
	{
		$this->db = Db::getInstance();
		
		if($attributs !== null){
			$request = $this->db->prepare($sql);
			$request->execute($attributs);
			return $request;
		}else{
			return $this->db->query($sql);
		}
	}
	
	// créer une entrée dans une table via des attributs définis
	public function save()
	{	
		$model_vars = get_object_vars($this);
		
		foreach($model_vars as $attr => $value){
			if($value !== null && !in_array($attr,$this->modelAttr) && $attr != $this->primaryKey){
				$columns[] = $attr;
				$bind[] = '?';
				$values[] = $value;
			}
		}
		
		$columns = implode(', ',$columns);
		$bind = implode(', ',$bind);
		
		return $this->request('INSERT INTO '.$this->table.' ('.$columns.') VALUES ('.$bind.')',$values);
	}
	
	// créer une entrée à partir d'un tableau de données (hydratation)
	// les champs fillable ou guarded doivent être définis
	public function create(array $data)
	{	
		foreach($data as $key => $value){
			if($this->guarded === [] && $this->fillable === []){
				$this->$key = $value;
			}else{
				if($this->fillable !== []){
					if($this->guarded !== NULL){
						if(in_array($key, $this->fillable) && !in_array($key, $this->guarded)){
							$this->$key = $value;
						}
					}else{
						if(in_array($key, $this->fillable)){
							$this->$key = $value;
						}
					}
				}else{
					if($this->guarded !== NULL){
						if(!in_array($key, $this->guarded)){
							$this->$key = $value;
						}
					}
				}
				
			}
		}

		return $this->save();
	}
	
	//récupérer l'ensemble des entrées d'une table
	public function all()
	{
		$request = $this->request('SELECT * FROM '. $this->table);
		return $request->fetchAll(PDO::FETCH_OBJ);
	}
	
	//récupérer une entrée d'une table via sa clé primaire
	public function find(int $id)
	{
		$request = $this->request('SELECT * FROM '. $this->table .' WHERE '.$this->primaryKey.' = '.$id);
		$request->setFetchMode(PDO::FETCH_INTO, $this);
		return $request->fetch();
	}
	
	//mettre à jour une entrée d'une table via sa clé primaire
	public function update(int $id)
	{
		$model_vars = get_object_vars($this);
		
		foreach($model_vars as $attr => $value){
			if($value !== null && !in_array($attr,$this->modelAttr) && $attr != $this->primaryKey){
				$columns[] = $attr. ' = ?';
				$values[] = $value;
			}
		}
		
		$columns = implode(', ',$columns);
		
		return $this->request('UPDATE '.$this->table.' SET '.$columns.' WHERE '.$this->primaryKey.' = '.$id,$values);
	}
	
	//supprimer l'entrée en base de l'instance "sélectionnée" du modèle
	public function delete()
	{
		$key = $this->primaryKey;
		$id = $this->$key;
		
		return $this->request('DELETE FROM '.$this->table.' WHERE '.$key.' = ?',[$id]);// on protège cette donnée en provoquant un prepare au lieu d'un query
	}
	
	//supprimer une entrée d'une table via sa clé primaire
	public function destroy(int $id)
	{	
		return $this->request('DELETE FROM '.$this->table.' WHERE '.$this->primaryKey.' = ?',[$id]);// on protège cette donnée en provoquant un prepare au lieu d'un query
	}
	
	// retrocompatibilité
    protected function dbConnectPDO()
    {
		return $this->db = Db::getInstance();
	}
}
