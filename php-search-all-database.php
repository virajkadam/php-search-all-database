<?php

$search_keyword = 'KEYWORD'; // Keyword to search for in configured columns.

// Tables and columns to search.
// Keys are table names, values are the searchable columns for each table.
$table_associative_array = [
    'TABLE_NAME_1' => [
        'column_name_a',
        'column_name_b',
    ],
    'TABLE_NAME_2' => [
        'column_name_a',
        'column_name_b',
    ],
];

php_search_all_database($search_keyword, $table_associative_array);

/**
 * Searches configured tables/columns for a keyword.
 *
 * Optimization notes:
 * - Executes one query per table (using OR conditions) instead of one query per column.
 * - Uses prepared statements for keyword values.
 * - Escapes table/column identifiers defensively.
 *
 * @param string $search_keyword Keyword to search for.
 * @param array<string, array<int, string>> $table_associative_array Table => columns mapping.
 */
function php_search_all_database($search_keyword, $table_associative_array)
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
        return;
    }

    if (empty($table_associative_array)) {
        echo 'No tables configured to search.';
        return;
    }

    echo '<b>Given Keyword:</b> ' . htmlspecialchars($search_keyword, ENT_QUOTES, 'UTF-8') . '<br>';
    echo '<b>Given tables:</b> ' . htmlspecialchars(implode(', ', array_keys($table_associative_array)), ENT_QUOTES, 'UTF-8') . '<br><hr>';

    $totalMatches = 0;
    $likeValue = '%' . $search_keyword . '%';

    foreach ($table_associative_array as $table_name => $column_names) {
        if (empty($column_names)) {
            continue;
        }

        // Escape identifiers for MySQL (tables/columns cannot be bound as prepared statement params).
        $safeTable = '`' . str_replace('`', '``', $table_name) . '`';

        $whereClauses = [];
        $params = [];
        $types = '';

        foreach ($column_names as $column) {
            $safeColumn = '`' . str_replace('`', '``', $column) . '`';
            $whereClauses[] = $safeColumn . ' LIKE ?';
            $params[] = $likeValue;
            $types .= 's';
        }

        $sql = 'SELECT * FROM ' . $safeTable . ' WHERE ' . implode(' OR ', $whereClauses);
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            echo '<b>Table:</b> ' . htmlspecialchars($table_name, ENT_QUOTES, 'UTF-8') . ' - query prepare failed.<br>';
            continue;
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);

        if (!mysqli_stmt_execute($stmt)) {
            echo '<b>Table:</b> ' . htmlspecialchars($table_name, ENT_QUOTES, 'UTF-8') . ' - query execution failed.<br>';
            mysqli_stmt_close($stmt);
            continue;
        }

        $result = mysqli_stmt_get_result($stmt);

        echo '<h4>Table: ' . htmlspecialchars($table_name, ENT_QUOTES, 'UTF-8') . '</h4>';

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<ul>';

                foreach ($column_names as $column) {
                    if (isset($row[$column]) && stripos((string)$row[$column], $search_keyword) !== false) {
                        $totalMatches++;
                        echo '<li><b>Column:</b> ' . htmlspecialchars($column, ENT_QUOTES, 'UTF-8') . '</li>';
                        echo '<li><b>Value:</b> ' . htmlspecialchars((string)$row[$column], ENT_QUOTES, 'UTF-8') . '</li>';
                    }
                }

                echo '</ul>';
            }
        } else {
            echo 'No matches found.<br>';
        }

        echo '<hr>';
        mysqli_stmt_close($stmt);
    }

    echo '<b>Total matched values:</b> ' . $totalMatches;

    mysqli_close($conn);
}
