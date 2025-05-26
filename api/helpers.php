<?php
// api/helpers.php
// Enhanced helper functions for Vercel deployment with PDO & MySQLi compatibility

/**
 * Check if the current environment is Vercel
 * 
 * @return bool True if running on Vercel
 */
function is_vercel_env() {
    static $is_vercel = null;
    
    if ($is_vercel === null) {
        $is_vercel = getenv('VERCEL') === '1';
        error_log("[HELPERS] Environment check: " . ($is_vercel ? 'Vercel' : 'Local'));
    }
    
    return $is_vercel;
}

/**
 * Get appropriate database connection (PDO for Vercel, mysqli for local)
 * 
 * @return mixed Database connection object or null if connection failed
 */
function get_db_connection() {
    global $pdo_connection, $mysqli_connection;
    
    // Use the appropriate connection based on environment
    if (is_vercel_env()) {
        return $pdo_connection;
    } else {
        return $mysqli_connection;
    }
}

/**
 * Execute a database query with appropriate syntax for the current environment
 * 
 * @param string $mysql_query MySQL query syntax
 * @param string $pgsql_query PostgreSQL query syntax (for Vercel/Supabase)
 * @param array $params Parameters for prepared statement
 * @return mixed Query result
 */
function execute_query($mysql_query, $pgsql_query = null, $params = []) {
    global $pdo_connection, $mysqli_connection;
    
    // If PostgreSQL query is not provided, use MySQL query
    if ($pgsql_query === null) {
        $pgsql_query = $mysql_query;
    }
    
    // Execute the appropriate query based on environment
    if (is_vercel_env()) {
        try {
            $stmt = $pdo_connection->prepare($pgsql_query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("[DB_ERROR] PDO Error: " . $e->getMessage());
            return false;
        }
    } else {
        try {
            $stmt = mysqli_prepare($mysqli_connection, $mysql_query);
            if ($stmt) {
                if (!empty($params)) {
                    // Determine types for bind_param
                    $types = '';
                    foreach ($params as $param) {
                        if (is_int($param)) {
                            $types .= 'i';
                        } elseif (is_float($param)) {
                            $types .= 'd';
                        } else {
                            $types .= 's';
                        }
                    }
                    
                    // Create references for bind_param
                    $bind_params = array();
                    $bind_params[] = &$types;
                    foreach ($params as &$param) {
                        $bind_params[] = &$param;
                    }
                    
                    // Call bind_param with references
                    call_user_func_array(array($stmt, 'bind_param'), $bind_params);
                }
                mysqli_stmt_execute($stmt);
                return $stmt;
            }
            return false;
        } catch (Exception $e) {
            error_log("[DB_ERROR] MySQLi Error: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Fetch all results from a query executed with execute_query
 * 
 * @param mixed $stmt Statement returned from execute_query
 * @return array Array of records or empty array if no results
 */
function fetch_all($stmt) {
    if (!$stmt) {
        return [];
    }
    
    if (is_vercel_env()) {
        // PDO approach
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // MySQLi approach
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

/**
 * Fetch a single row from a query executed with execute_query
 * 
 * @param mixed $stmt Statement returned from execute_query
 * @return array|null Single record or null if no results
 */
function fetch_one($stmt) {
    if (!$stmt) {
        return null;
    }
    
    if (is_vercel_env()) {
        // PDO approach
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } else {
        // MySQLi approach
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result) ?: null;
    }
}

/**
 * Get the count of affected rows from the last query
 * 
 * @param mixed $stmt Statement returned from execute_query
 * @return int Number of affected rows
 */
function affected_rows($stmt) {
    if (!$stmt) {
        return 0;
    }
    
    if (is_vercel_env()) {
        // PDO approach
        return $stmt->rowCount();
    } else {
        // MySQLi approach
        return mysqli_stmt_affected_rows($stmt);
    }
}

/**
 * Get the last inserted ID
 * 
 * @return int|string Last inserted ID or 0 if none
 */
function last_insert_id() {
    global $pdo_connection, $mysqli_connection;
    
    if (is_vercel_env()) {
        // PDO approach - PostgreSQL needs sequence name
        try {
            return $pdo_connection->lastInsertId();
        } catch (Exception $e) {
            error_log("[DB_ERROR] PDO LastInsertId Error: " . $e->getMessage());
            return 0;
        }
    } else {
        // MySQLi approach
        return mysqli_insert_id($mysqli_connection);
    }
}
?>
