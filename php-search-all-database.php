<?php

$search_keyword = 'KEYWORD'; // Keyword to search.

// Table => searchable columns mapping.
$table_associative_array = [
    'TABLE_NAME_1' => ['column_name_a', 'column_name_b'],
    'TABLE_NAME_2' => ['column_name_a', 'column_name_b'],
];

// Optional row-id column (set null to hide)
$row_identifier_column = 'id';

php_search_all_database($search_keyword, $table_associative_array, $row_identifier_column);

/**
 * Search keyword across multiple tables/columns.
 */
function php_search_all_database($search_keyword, $table_associative_array, $row_identifier_column = 'id')
{
    $conn = mysqli_connect('DATABASE HOST NAME', 'DATABASE USERNAME', 'DATABASE PASSWORD', 'DATABASE NAME');

    if (!$conn) {
        echo 'DB Connection Failed: ' . mysqli_connect_error();
        return;
    }

    if (trim($search_keyword) === '') {
        echo 'Keyword cannot be empty.';
        mysqli_close($conn);
        return;
    }

    if (empty($table_associative_array)) {
        echo 'No tables configured.';
        mysqli_close($conn);
        return;
    }

    echo '<b>Keyword:</b> ' . e($search_keyword) . '<br>';
    echo '<b>Tables:</b> ' . e(implode(', ', array_keys($table_associative_array))) . '<br><hr>';

    $total_matches = 0;
    $like_value = '%' . $search_keyword . '%';

    foreach ($table_associative_array as $table_name => $columns) {

        $safe_table = sanitize_identifier($table_name);
        $safe_columns = sanitize_identifiers($columns);

        if ($safe_table === null || empty($safe_columns)) {
            echo 'Invalid table/columns: ' . e($table_name) . '<br><hr>';
            continue;
        }

        // Build WHERE clause
        $where = [];
        $types = '';
        $params = [];

        foreach ($safe_columns as $col) {
            $where[] = "`$col` LIKE ?";
            $types .= 's';
            $params[] = $like_value;
        }

        $sql = "SELECT * FROM `$safe_table` WHERE " . implode(' OR ', $where);

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            echo 'Prepare failed: ' . e($table_name) . '<br><hr>';
            continue;
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);

        if (!mysqli_stmt_execute($stmt)) {
            echo 'Execution failed: ' . e($table_name) . '<br><hr>';
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

            $matched = false;
            $html = '';

            foreach ($safe_columns as $col) {
                if (!isset($row[$col])) continue;

                $value = (string)$row[$col];

                if (stripos($value, $search_keyword) !== false) {
                    $matched = true;
                    $total_matches++;

                    $html .= '<li><b>Column:</b> ' . e($col) . '</li>';
                    $html .= '<li><b>Value:</b> ' . e($value) . '</li>';
                }
            }

            if ($matched) {
                echo '<ul>';

                if ($row_identifier_column !== null && isset($row[$row_identifier_column])) {
                    echo '<li><b>Row:</b> ' . e($row[$row_identifier_column]) . '</li>';
                }

                echo $html;
                echo '</ul>';
            }
        }

        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        echo '<hr>';
    }

    echo '<b>Total Matches:</b> ' . $total_matches;

    mysqli_close($conn);
}

/**
 * Validate SQL identifier (table/column)
 */
function sanitize_identifier($name)
{
    return (is_string($name) && preg_match('/^[A-Za-z0-9_]+$/', $name)) ? $name : null;
}

/**
 * Validate multiple identifiers
 */
function sanitize_identifiers($names)
{
    $valid = [];

    foreach ($names as $name) {
        $clean = sanitize_identifier($name);
        if ($clean !== null) {
            $valid[$clean] = $clean;
        }
    }

    return array_values($valid);
}

/**
 * Safe HTML output
 */
function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}