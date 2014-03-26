<?php namespace Stillat\Database\SchemaCreator\Drivers;

use Stillat\Database\SchemaCreator\Drivers\PDOSchemaDriver;

class PostgresDriver extends PDOSchemaDriver {

	protected $pdoDriver = 'pgsql';

	protected $createSyntax = 'CREATE DATABASE `:SCHEMA`;';

	protected $dropSyntax   = 'DROP DATABASE `:SCHEMA`;';
	
}