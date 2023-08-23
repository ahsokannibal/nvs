<?php
require_once($_SERVER['DOCUMENT_ROOT']."/mvc/Db/Db.php");

abstract class Model extends Db
{
	
	protected $table;
	protected $primaryKey = 'id';
	protected $fillable = []; // seuls les champs de ce tableau seront autorisés dans une hydratation
	protected $guarded; //  tous les champs de ce tableau seront enlevés d'une hydratation
	
	private $modelAttr = ['table','primaryKey','fillable','guarded','modelAttr','selectedCols','whereConditions','joinedTables','groupByConditions','db'];
	private $selectedCols = '';
	private $whereConditions = [];
	private $joinedTables = '';
	private $groupByConditions = '';
	private $db;
	
	public function __construct()
	{
		// par défaut la table est le nom de la classe
		if(empty($this->table)){
			$this->table = get_class($this);
		}
	}
	
	/**
     * Display the generic request
     * @sql string
	 * @attributs array=null
     * @return database response
     */
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
	
	/*
	 * get all resources from the database. Can be used with Select, Where, Join functions, Group
	 * @return object
	*/
	public function get(){
		
		// traitement des tableaux selected, where, with
		$selected = (empty($this->selectedCols))?'*':$this->selectedCols;
		$joined = (empty($this->joinedTables))?'':$this->joinedTables;
		$grouped = (empty($this->groupByConditions))?'':' GROUP BY '.$this->groupByConditions;
		
		if(empty($this->whereConditions)){
			$where = '';
			$values = null;
		}else{
			$columns = [];
			$values = [];
			
			foreach($this->whereConditions as $condition){
				$columns[] = $condition[0];
				$values[] = $condition[1];
			}
			
			$where = ' WHERE '.implode(', ',$columns);
		}

		$query = 'SELECT '.$selected.' FROM '.$this->table.$joined.$where.$grouped;
		
		$request = $this->request($query,$values);

		return $request->fetchAll(PDO::FETCH_CLASS);
	}
	
	//récupérer une entrée d'une table via sa clé primaire (à améliorer comme le get)
	public function find(int $id)
	{
		$selected = (empty($this->selectedCols))?'*':$this->selectedCols;
		$joined = (empty($this->joinedTables))?'':$this->joinedTables;
		
		$request = $this->request('SELECT '.$selected.' FROM '.$this->table.$joined.' WHERE '.$this->table.'.'.$this->primaryKey.' = '.$id);
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
	
	/*
	 * delete a resource by the primary key. Accept an array of primary keys to multiple delete
	 * @return request
	*/
	public function destroy($ids)
	{	
		if(is_array($ids)){
			
			foreach($ids as $id){
				$columns[] = '?';
			}
			$columns = implode(', ',$columns);
			$whereIn = 'IN ('.$columns.')';
			
		}elseif(is_int($ids)){
			$whereIn = '= ?';
			$ids = [$ids];
		}
		
		$query = 'DELETE FROM '.$this->table.' WHERE '.$this->primaryKey.' '.$whereIn;
		
		return $this->request($query,$ids);// on protège cette donnée en provoquant un prepare au lieu d'un query
	}
	
	/* Ajouter une sélection d'attributs à la requête
	 * 
	 * @return *this
	 */
	public function select(...$attributs){
		
		$columns = [];
		foreach($attributs as $attr){
			$columns[] = $attr;
		}
		
		$columns = implode(', ',$columns);
		$this->selectedCols = $columns; 
		
		return $this;
	}
	
	/**
	 * Ajouter une condition WHERE à la requête
	 * 
	 * @return *this
	 */
	public function where(string $attr, string $operator, $value=null){
		
		if(!$value){
			$value = $operator;
			$operator = '=';
		}
		$condition = $attr.' '.$operator.' ?';

		$this->whereConditions[] = [$condition,$value];
		
		return $this;
		
	}
	
		
	/**
     * Ajouter un groupement de résultats (GROUP BY) à la requête
     * 
     * @return $this
     */
	public function groupBy(...$attributs){
		
		$columns = [];
		foreach($attributs as $attr){
			$columns[] = $attr;
		}
		
		$columns = implode(', ',$columns);
		$this->groupByConditions = $columns; 
		
		return $this;

	}
	
	/**
     * Ajouter une jointure interne (INNER JOIN) à la requête
     * 
     * @return $this
     */
	public function join(string $joined_table, string $contraint_1,string $operator,string $contraint_2){
		
		$query = ' INNER JOIN '.$joined_table.' ON '.$contraint_1.$operator.$contraint_2;

		$this->joinedTables .= $query;
		
		return $this;
		
	}
	
	/**
     * Ajouter une jointure gauche (LEFT JOIN) à la requête // Séparée de la jointure interne pour plus de clareté
     * 
     * @return $this
     */
	public function leftJoin(string $joined_table, string $contraint_1,string $operator,string $contraint_2){
		
		$query = ' LEFT JOIN '.$joined_table.' ON '.$contraint_1.$operator.$contraint_2;

		$this->joinedTables .= $query;
		
		return $this;

	}
	
	// retrocompatibilité
    protected function dbConnectPDO()
    {
		return $this->db = Db::getInstance();
	}
}
