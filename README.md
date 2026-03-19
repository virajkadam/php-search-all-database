# PHP Search All Database

A lightweight PHP script to search multiple MySQL tables/columns using a simple configuration.

## Latest updates

- Resolved merge-style divergence and kept a single clean implementation.
- Kept the main optimization: **one prepared query per table** using OR-ed `LIKE` conditions.
- Added/kept **relevant inline comments** for setup, optimization, and output behavior.
- Added strict **table/column identifier validation** (`[A-Za-z0-9_]`) before query construction.
- Added optional `$row_identifier_column` support to print row id values when available.
- Kept HTML escaping for safer browser output.

## Configure

In `php-search-all-database.php`, set:

```php
$search_keyword = 'KEYWORD';

$table_associative_array = [
    'TABLE_NAME_1' => ['column_name_a', 'column_name_b'],
    'TABLE_NAME_2' => ['column_name_a', 'column_name_b'],
];

$row_identifier_column = 'id'; // Set null to hide row id
```

Also update DB credentials inside `php_search_all_database()`:

```php
$db_hostname = 'DATABASE HOST NAME';
$db_username = 'DATABASE USERNAME';
$db_password = 'DATABASE PASSWORD';
$db_database_name = 'DATABASE NAME';
```

## Run

```php
php_search_all_database($search_keyword, $table_associative_array, $row_identifier_column);
```

## Notes

- Script remains schema-agnostic and uses `SELECT *`.
- Add indexes on searched columns for better performance on large datasets.
- `mysqli_stmt_get_result()` requires mysqlnd support.
