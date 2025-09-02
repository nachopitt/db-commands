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
php artisan db:create {name?}
```

| Argument | Description |
|---|---|
| `name` | The name of the database to create. If not provided, the command will use the database name from your `database.php` config file. |

### `db:drop`

Drop an existing MySQL database.

```bash
php artisan db:drop {name?}
```

| Argument | Description |
|---|---|
| `name` | The name of the database to drop. If not provided, the command will use the database name from your `database.php` config file. |

### `db:export`

Export an existing MySQL database into SQL statements.

```bash
php artisan db:export {schema?}
```

| Argument | Description |
|---|---|
| `schema` | The name of the database to export. If not provided, the command will use the database name from your `database.php` config file. |

### `db:import`

Import a SQL file into an existing MySQL database.

```bash
php artisan db:import {file?} {--s|schema=} {--i|ignore-foreign-key-checks}
```

| Argument | Description |
|---|---|
| `file` | The path to the SQL file to import. If not provided, the command will use `database_model/{database}.sql`. |
| `--schema` | The name of the database to import into. If not provided, the command will use the database name from your `database.php` config file. |
| `--ignore-foreign-key-checks` | Ignore foreign key checks while importing. |

### `db:truncate`

Truncate tables from database.

```bash
php artisan db:truncate {tables?} {--s|schema=} {--i|ignore-foreign-key-checks}
```

| Argument | Description |
|---|---|
| `tables` | A comma-separated list of tables to truncate. If not provided, the command will truncate all tables in the database. |
| `--schema` | The name of the database to truncate tables from. If not provided, the command will use the database name from your `database.php` config file. |
| `--ignore-foreign-key-checks` | Ignore foreign key checks while truncating. |

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
