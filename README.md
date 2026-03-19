# PHP Search All Database

A lightweight PHP utility to search multiple MySQL tables/columns for a keyword with a simple config-driven setup.

## What changed in the latest optimization

- **One query per table** (using OR-ed `LIKE` clauses) instead of one query per column.
- **Prepared statements** for all keyword values.
- **Identifier validation** for table/column names (only letters, numbers, underscore).
- **Optional row-id display** via `$row_identifier_column` (defaults to `id`).
- **Cleaner structure** with helper functions:
  - `execute_table_search()`
  - `get_matched_columns()`
  - `sanitize_identifier()` / `sanitize_identifiers()`
  - `e()` and `print_line()`
- **Safer output** using HTML escaping.

## Configuration

Update these values in `php-search-all-database.php`:

```php
$search_keyword = 'KEYWORD';

$table_associative_array = [
    'TABLE_NAME_1' => ['column_name_a', 'column_name_b'],
    'TABLE_NAME_2' => ['column_name_a', 'column_name_b'],
];

$row_identifier_column = 'id'; // Set to null to hide row id in output
```

Also configure database credentials in the function:

```php
$db_hostname = 'DATABASE HOST NAME';
$db_username = 'DATABASE USERNAME';
$db_password = 'DATABASE PASSWORD';
$db_database_name = 'DATABASE NAME';
```

## Usage

Run the file after updating config values:

```php
php_search_all_database($search_keyword, $table_associative_array, $row_identifier_column);
```

## Notes

- This script is schema-agnostic and currently uses `SELECT *` for compatibility.
- If your dataset is large, consider adding pagination/limits or indexing searched columns.
- `mysqli_stmt_get_result()` requires mysqlnd in your PHP installation.
