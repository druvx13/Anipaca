<?php
// Include your DB connection here if not already done
require_once '_config.php'; // Make sure this sets up $conn

function importDatabaseIfMissing($conn, $sqlFilePath) {
    // List one or more essential tables to check for
    $requiredTables = ['users', 'comments'];

    // Check if at least one required table exists
    $existingTables = [];
    try {
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch_row()) {
                $existingTables[] = $row[0];
            }
            // It's good practice to free the result if it's a SELECT query
            if (is_object($result)) $result->free();
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Failed to show tables: " . $e->getMessage());
        echo "Error checking tables: " . $e->getMessage();
        return; // Exit function if we can't check tables
    }

    // Check if any required table is missing
    $missing = false;
    foreach ($requiredTables as $table) {
        if (!in_array($table, $existingTables)) {
            $missing = true;
            break;
        }
    }

    // If missing, import the SQL file
    if ($missing) {
        if (!file_exists($sqlFilePath)) {
            die("SQL file not found: " . htmlspecialchars($sqlFilePath));
        }

        $sql = file_get_contents($sqlFilePath);
        try {
            if ($conn->multi_query($sql)) {
                do {
                    // Flush multi_query results
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->next_result()); // Check for more results

                echo "✅ Database imported successfully from {$sqlFilePath}";
            }
            // No 'else' needed here because if multi_query fails with MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT,
            // it will throw an exception, and the catch block will handle it.
        } catch (mysqli_sql_exception $e) {
            die("❌ Error importing database: " . $e->getMessage());
        }
    } else {
        echo "✔️ Database already initialized.";
    }
}

// Call it (adjust path if needed)
importDatabaseIfMissing($conn, __DIR__ . '/database.sql');
