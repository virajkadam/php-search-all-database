<?php

$search_keyword = 'KEYWORD'; // Keyword to search.

// Table => searchable columns mapping.
$table_associative_array = [
    'TABLE_NAME_1' => ['column_name_a', 'column_name_b'],
    'TABLE_NAME_2' => ['column_name_a', 'column_name_b'],
];

// Optional row-id column to display in output. Set null to hide row id.
$row_identifier_column = 'id';

php_search_all_database($search_keyword, $table_associative_array, $row_identifier_column);

/**
 * Search a keyword across configured table/column pairs.
 *
 * @param string $search_keyword Keyword to search.
 * @param array<string, array<int, string>> $table_associative_array Table => columns mapping.
 * @param string|null $row_identifier_column Optional row-id column to print.
 * @return void
 */
function php_search_all_database($search_keyword, $table_associative_array, $row_identifier_column = 'id')
{
    $db_hostname = 'DATABASE HOST NAME';
    $db_username = 'DATABASE USERNAME';
    $db_password = 'DATABASE PASSWORD';
    $db_database_name = 'DATABASE NAME';

    $conn = mysqli_connect($db_hostname, $db_username, $db_password, $db_database_name);
    if (!$conn) {
        echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
        return;
    }

    if (trim($search_keyword) === '') {
        echo 'Please provide a non-empty keyword.';
        mysqli_close($conn);
        return;
    }

    if (empty($table_associative_array)) {
        echo 'No tables configured to search.';
        mysqli_close($conn);
        return;
    }

    echo '<b>Given Keyword:</b> ' . e($search_keyword) . '<br>';
    echo '<b>Given tables:</b> ' . e(implode(', ', array_keys($table_associative_array))) . '<br><hr>';

    $total_matches = 0;
    $like_value = '%' . $search_keyword . '%';

    foreach ($table_associative_array as $table_name => $columns) {
        $safe_table_name = sanitize_identifier($table_name);
        $safe_columns = sanitize_identifiers($columns);

        if ($safe_table_name === null || empty($safe_columns)) {
            echo '<b>Skipped:</b> Invalid table/column config for <i>' . e((string)$table_name) . '</i>.<br><hr>';
            continue;
        }

        // Optimization: one prepared query per table (OR over all configured columns).
        $where_clauses = [];
        $param_types = '';
        $params = [];

        foreach ($safe_columns as $column) {
            $where_clauses[] = '`' . $column . '` LIKE ?';
            $param_types .= 's';
            $params[] = $like_value;
        }

        $sql = 'SELECT * FROM `' . $safe_table_name . '` WHERE ' . implode(' OR ', $where_clauses);
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            echo '<b>Table:</b> ' . e($table_name) . ' - query prepare failed.<br><hr>';
            continue;
        }

        mysqli_stmt_bind_param($stmt, $param_types, ...$params);

        if (!mysqli_stmt_execute($stmt)) {
            echo '<b>Table:</b> ' . e($table_name) . ' - query execution failed.<br><hr>';
            mysqli_stmt_close($stmt);
            continue;
        }

        $result = mysqli_stmt_get_result($stmt);
        echo '<h4>Table: ' . e($table_name) . '</h4>';

        if (!$result || mysqli_num_rows($result) === 0) {
            echo 'No matches found.<br><hr>';
            mysqli_stmt_close($stmt);
            continue;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $has_match_in_row = false;
            $row_html = '';

            foreach ($safe_columns as $column) {
                if (!isset($row[$column])) {
                    continue;
                }

                $value = (string)$row[$column];
                if (stripos($value, $search_keyword) === false) {
                    continue;
                }

                $has_match_in_row = true;
                $total_matches++;
                $row_html .= '<li><b>Column:</b> ' . e($column) . '</li>';
                $row_html .= '<li><b>Value:</b> ' . e($value) . '</li>';
            }

            if ($has_match_in_row) {
                echo '<ul>';
                if ($row_identifier_column !== null && isset($row[$row_identifier_column])) {
                    echo '<li><b>Row:</b> ' . e((string)$row[$row_identifier_column]) . '</li>';
                }
                echo $row_html;
                echo '</ul>';
            }
        }

        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        echo '<hr>';
    }

    echo '<b>Total matched values:</b> ' . $total_matches;
    mysqli_close($conn);
}

/**
 * Validate one SQL identifier.
 * Allowed chars: letters, numbers, underscore.
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
 * Validate and deduplicate a list of SQL identifiers.
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
 * Escape output for safe HTML rendering.
 *
 * @param string $value
 * @return string
 */
function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
