<?php namespace Stillat\Database\SchemaCreator\Drivers;

use Stillat\Database\SchemaCreator\Drivers\PDOSchemaDriver;

class SqlServerDriver extends PDOSchemaDriver {

	protected $pdoDriver = 'sqlsrv';

	protected $createSyntax = 'CREATE DATABASE `:SCHEMA`;';

	protected $dropSyntax   = 'DROP DATABASE `:SCHEMA`;';
	
}