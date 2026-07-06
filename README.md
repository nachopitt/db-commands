# DB Commands

A set of Laravel Artisan commands for database management.

## Installation

You can install the package via composer:

```bash
composer require nachopitt/db-commands
```

## Usage

### `db:create`

Create a new MySQL database.

```bash
php artisan db:create {name?} {--c|connection=}
```

| Argument / Option | Description |
|---|---|
| `name` | The name of the MySQL database to create. If not provided, the command will use the database name from your configuration. |
| `--connection` | The database connection to use (e.g. `mysql`). Defaults to the active default connection. |

### `db:drop`

Drop an existing MySQL database.

```bash
php artisan db:drop {name?} {--c|connection=}
```

| Argument / Option | Description |
|---|---|
| `name` | The name of the MySQL database to drop. If not provided, the command will use the database name from your configuration. |
| `--connection` | The database connection to use. Defaults to the active default connection. |

### `db:export`

Export an existing MySQL database into SQL statements.

```bash
php artisan db:export {schema?} {--c|connection=} {--skip-ssl}
```

| Argument / Option | Description |
|---|---|
| `schema` | The name of the MySQL database to export. If not provided, the command will use the database name from your configuration. |
| `--connection` | The database connection to use. Defaults to the active default connection. |
| `--skip-ssl` | Bypass SSL certificate verification for the `mysqldump` command. |

### `db:import`

Import a SQL file into an existing MySQL database.

```bash
php artisan db:import {file?} {--s|schema=} {--i|ignore-foreign-key-checks} {--c|connection=}
```

| Argument / Option | Description |
|---|---|
| `file` | The path to the SQL file to import. If not provided, the command will use `database_model/{database}.sql`. |
| `--schema` | The name of the MySQL database to import into. If not provided, the command will use the database name from your configuration. |
| `--ignore-foreign-key-checks` | Ignore foreign key checks while importing. |
| `--connection` | The database connection to use. Defaults to the active default connection. |

### `db:truncate`

Truncate tables from a MySQL database.

```bash
php artisan db:truncate {tables?} {--s|schema=} {--i|ignore-foreign-key-checks} {--c|connection=}
```

| Argument / Option | Description |
|---|---|
| `tables` | A comma-separated list of tables to truncate. If not provided, the command will truncate all tables in the database. |
| `--schema` | The name of the MySQL database to truncate tables from. If not provided, the command will use the database name from your configuration. |
| `--ignore-foreign-key-checks` | Ignore foreign key checks while truncating. |
| `--connection` | The database connection to use. Defaults to the active default connection. |

## Testing

You can run the automated tests via:

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
