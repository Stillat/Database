<?php namespace Stillat\Database\Tenant;

use Illuminate\Foundation\Application;
use Stillat\Common\Exceptions\InvalidArgumentException;
use Stillat\Database\SchemaCreator\SchemaCreatorManager;

class TenantManager {

	/**
	 * The name of the tenancy session directive.
	 *
	 * @var string
	 */
	const TENANT_SESSION_DIRECTIVE_NAME = 'tenancy.connectionName';

	/**
	 * The separator to use for separating schema names from prefixes.
	 *
	 * @var string
	 */
	const TENANT_SCHEMA_SEPARATOR = '_';

	/**
	 * The name of the Laravel configuration directive for database connections.
	 *
	 * @var string
	 */
	const CONFIGURATION_KEY_NAME_PREFIX = 'database.connections';

	/**
	 * The name of the Laravel configuration directive for default database connections.
	 *
	 * @var string
	 */
	const CONFIGURATION_DEFAULT_CONNECTION_NAME = 'database.default';

	/**
	 * The fully qualified name of the Laravel migration base class.
	 *
	 * @var string
	 */
	const LARAVEL_MIGRATION_BASE_CLASS = 'Illuminate\Database\Migrations\Migration';

}