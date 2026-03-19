<?php

$search_keyword = 'KEYWORD'; // Keyword to search.

// Table => searchable columns mapping.
$table_associative_array = [
    'TABLE_NAME_1' => ['column_name_a', 'column_name_b'],
    'TABLE_NAME_2' => ['column_name_a', 'column_name_b'],
];

// Optional row identifier column to display in output (set to null to hide).
$row_identifier_column = 'id';

php_search_all_database($search_keyword, $table_associative_array, $row_identifier_column);

/**
 * Search a keyword in configured tables/columns.
 *
 * Key improvements:
 * - One prepared query per table (instead of one query per column).
 * - Strict validation for table/column identifiers.
 * - Optional row identifier output.
 * - Centralized, escaped HTML output helpers.
 *
 * @param string $search_keyword Keyword to search.
 * @param array<string, array<int, string>> $table_associative_array Table => columns mapping.
 * @param string|null $row_identifier_column Optional column to show as row id.
 */
function php_search_all_database($search_keyword, $table_associative_array, $row_identifier_column = 'id')
{
    $db_hostname = 'DATABASE HOST NAME';
    $db_username = 'DATABASE USERNAME';
    $db_password = 'DATABASE PASSWORD';
    $db_database_name = 'DATABASE NAME';

    $conn = mysqli_connect($db_hostname, $db_username, $db_password, $db_database_name);

    if (!$conn) {
        print_line('Failed to connect to MySQL: ' . mysqli_connect_error());
        return;
    }

    if (trim($search_keyword) === '') {
        print_line('Please provide a non-empty keyword.');
        mysqli_close($conn);
        return;
    }

    if (empty($table_associative_array)) {
        print_line('No tables configured to search.');
        mysqli_close($conn);
        return;
    }

    print_line('<b>Given Keyword:</b> ' . e($search_keyword));
    print_line('<b>Given tables:</b> ' . e(implode(', ', array_keys($table_associative_array))));
    print_line('<hr>');

    $total_matches = 0;
    $like_value = '%' . $search_keyword . '%';

    foreach ($table_associative_array as $table_name => $column_names) {
        $sanitized_columns = sanitize_identifiers($column_names);
        $sanitized_table = sanitize_identifier($table_name);

        if ($sanitized_table === null || empty($sanitized_columns)) {
            print_line('<b>Skipped:</b> Invalid table or column name in config for <i>' . e((string)$table_name) . '</i>.');
            print_line('<hr>');
            continue;
        }

        $result = execute_table_search($conn, $sanitized_table, $sanitized_columns, $like_value);

        if ($result === false) {
            print_line('<b>Table:</b> ' . e($table_name) . ' - query failed.');
            print_line('<hr>');
            continue;
        }

        print_line('<h4>Table: ' . e($table_name) . '</h4>');

        if (mysqli_num_rows($result) === 0) {
            print_line('No matches found.');
            print_line('<hr>');
            continue;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $matched_columns = get_matched_columns($row, $sanitized_columns, $search_keyword);
            if (empty($matched_columns)) {
                continue;
            }

            print_line('<ul>');

            if ($row_identifier_column !== null && isset($row[$row_identifier_column])) {
                print_line('<li><b>Row:</b> ' . e((string)$row[$row_identifier_column]) . '</li>');
            }

            foreach ($matched_columns as $column => $value) {
                $total_matches++;
                print_line('<li><b>Column:</b> ' . e($column) . '</li>');
                print_line('<li><b>Value:</b> ' . e($value) . '</li>');
            }

            print_line('</ul>');
        }

        mysqli_free_result($result);
        print_line('<hr>');
    }

    print_line('<b>Total matched values:</b> ' . $total_matches);
    mysqli_close($conn);
}

/**
 * Executes one table-level search with OR-ed LIKE clauses.
 *
 * @param mysqli $conn Active database connection.
 * @param string $table_name Already validated table name.
 * @param array<int, string> $column_names Already validated column names.
 * @param string $like_value LIKE pattern (e.g. "%term%").
 * @return mysqli_result|false
 */
function execute_table_search($conn, $table_name, $column_names, $like_value)
{
    $where_clauses = [];
    $types = '';
    $params = [];

    foreach ($column_names as $column) {
        $where_clauses[] = '`' . $column . '` LIKE ?';
        $types .= 's';
        $params[] = $like_value;
    }

    // Select all columns so current script remains schema-agnostic.
    $sql = 'SELECT * FROM `' . $table_name . '` WHERE ' . implode(' OR ', $where_clauses);
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }

    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

/**
 * Returns only the columns in a row whose values contain the keyword.
 *
 * @param array<string, mixed> $row
 * @param array<int, string> $column_names
 * @param string $search_keyword
 * @return array<string, string>
 */
function get_matched_columns($row, $column_names, $search_keyword)
{
    $matches = [];

    foreach ($column_names as $column) {
        if (!isset($row[$column])) {
            continue;
        }

        $value = (string)$row[$column];
        if (stripos($value, $search_keyword) !== false) {
            $matches[$column] = $value;
        }
    }

    return $matches;
}

/**
 * Validates one SQL identifier and returns it if valid.
 * Allowed: letters, numbers, underscore.
 *
 * @param mixed $name
 * @return string|null
 */
function sanitize_identifier($name)
{
    if (!is_string($name)) {
        return null;
    }

    return preg_match('/^[A-Za-z0-9_]+$/', $name) ? $name : null;
}

/**
 * Validates and deduplicates a list of SQL identifiers.
 *
 * @param array<int, mixed> $names
 * @return array<int, string>
 */
function sanitize_identifiers($names)
{
    $output = [];

    foreach ($names as $name) {
        $validated_name = sanitize_identifier($name);
        if ($validated_name !== null) {
            $output[$validated_name] = $validated_name;
        }
    }

    return array_values($output);
}

/**
 * HTML-escapes a string for safe output.
 *
 * @param string $value
 * @return string
 */
function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Prints one HTML line with a trailing <br>.
 *
 * @param string $html
 * @return void
 */
function print_line($html)
{
    echo $html . '<br>';
}
