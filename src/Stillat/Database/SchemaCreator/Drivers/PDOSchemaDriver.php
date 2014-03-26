<?php namespace Stillat\Database\SchemaCreator\Drivers;

use PDO, Exception;
use Stillat\Database\Exceptions\DatabaseException;
use Stillat\Common\Exceptions\InvalidArgumentException;
use Stillat\Database\SchemaCreator\SchemaCreatorInterface;

class PDOSchemaDriver implements SchemaCreatorInterface {

	/**
	 * The PDO driver name
	 * 
	 * @var string
	 */
	protected $pdoDriver = '';

	/**
	 * The syntax for the create database statement.
	 * 
	 * @var string
	 */
	protected $createSyntax = '';

	/**
	 * The syntax for the drop database statement.
	 * 
	 * @var string
	 */
	protected $dropSyntax = '';

	/**
	 * The connection host name.
	 * 
	 * @var string
	 */
	protected $host = '';

	/**
	 * The connection user name.
	 * 
	 * @var string
	 */
	protected $username = '';

	/**
	 * The connection password.
	 * 
	 * @var string
	 */
	protected $password = '';

	/**
	 * Returns a new instance of PDOSchemaDriver
	 * 
	 * @param  string $host
	 * @param  string $username
	 * @param  string $password
	 * @return \Stillat\Database\SchemaCreator\Drivers\PDOSchemaDriver
	 */
	public function __construct($host, $username, $password)
	{
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Returns the PDO Connection
	 * 
	 * @return
	 */
	protected function getConnection()
	{
		return $this->pdoDriver;
	}

	/**
	 * Builds a new PDO Connection
	 * 
	 * @param  string $host
	 * @param  string $username
	 * @param  string $password
	 * @return 
	 */
	protected function buildConnection($host, $username, $password)
	{
		try
		{
			$connection = new PDO($this->getConnection().':host='.$host, $username, $password);
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
			return $connection;
		} catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Creates a new database schema.
	 *
	 * @param  string $schemaName
	 * @param  string $host
	 * @param  string $username
	 * @param  string $password
	 * @return bool
	 */
	public function createSchema($schemaName)
	{
		$this->validateSchemaName($schemaName);
		
		try
		{
			$connection = $this->buildConnection($this->host, $this->username, $this->password);
			$query = $connection->prepare($this->convertSyntax($this->createSyntax, $schemaName));
			$query->execute(array($schemaName));

			return true;
		} catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Drops the schema with a given name.
	 * 
	 * @param  string $schemaName
	 * @return bool
	 */
	public function dropSchema($schemaName)
	{
		$this->validateSchemaName($schemaName);
		
		try
		{
			$connection = $this->buildConnection($this->host, $this->username, $this->password);
			$query = $connection->prepare($this->convertSyntax($this->dropSyntax, $schemaName));
			$query->execute(array($schemaName));

			return true;
		} catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Converts the statement syntax into a valid statement.
	 * 
	 * @param  string $sql
	 * @param  string $schema
	 * @return string
	 */
	protected function convertSyntax($sql, $schema)
	{
		return str_replace(':SCHEMA', $schema, $sql);
	}

	/**
	 * Validates a schema name.
	 * 
	 * @param  string $schema
	 * @return void
	 */
	protected function validateSchemaName($schema)
	{
		if (strlen(trim($schema)) > 35)
		{
			throw new InvalidArgumentException("Invalid schema name '{$schema}'. Schema name too long.");
		}

		if (str_contains($schema, '_'))
		{
			$schemaParts = explode('_', $schema);

			$schemaPrefix = $schemaParts[0];
			$schemaName   = $schemaParts[1];

			if (strlen(trim($schemaPrefix)) !== 2)
			{
				throw new InvalidArgumentException("Invalid schema prefix '{$schemaPrefix}'. Schema prefix too long.");
			}

			if (strlen(trim($schemaName)) !== 32)
			{
				throw new InvalidArgumentException("Invalid schema suffix '{$schemaName}'. Schema Suffix too long.");
			}
		}
	}

}