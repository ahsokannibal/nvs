<?php

require_once("Model.php");

class Exemple extends Model
{
	protected $table = "batiment";
	protected $primaryKey = "id_batiment";
	// protected $fillable = ['test'];
	protected $guarded = [];
	
}