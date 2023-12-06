<?php
require_once("Model.php");

class Unit extends Model
{
	protected $table = "type_unite";
	protected $primaryKey = "id_unite";
	// protected $fillable = [];
	protected $guarded = [];
}