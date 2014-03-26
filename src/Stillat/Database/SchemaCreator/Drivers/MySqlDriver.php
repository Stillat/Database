<?php namespace Stillat\Database\SchemaCreator\Drivers;

use Stillat\Database\SchemaCreator\Drivers\PDOSchemaDriver;

class MySqlDriver extends PDOSchemaDriver {

	protected $pdoDriver = 'mysql';

	protected $createSyntax = 'CREATE DATABASE IF NOT EXISTS `:SCHEMA`;';

	protected $dropSyntax   = 'DROP DATABASE IF EXISTS `:SCHEMA`;';
	
}