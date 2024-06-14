<?php
require_once 'config.php';

// Function to safely execute prepared statements
function executeQuery($sql, $params = []) {
    global $link;

    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        return false;
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, ...$params);
    }

    $result = mysqli_stmt_execute($stmt);

    if ($result === false) {
        return false;
    }

    return $stmt;
}

// Function to fetch single row from query result
function fetchSingle($stmt) {
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Function to fetch multiple rows from query result
function fetchMultiple($stmt) {
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}
?>
