<?php
require_once(__DIR__ . '/../_config.php');

function run_migrations($conn) {
    // Create migrations table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS `migrations` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `migration` varchar(255) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Get all migration files
    $migration_files = glob(__DIR__ . '/../migrations/*.sql');

    // Get migrations that have already been run
    $result = $conn->query("SELECT migration FROM migrations");
    $run_migrations = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $run_migrations[] = $row['migration'];
        }
    }

    // Run new migrations
    foreach ($migration_files as $file) {
        $migration_name = basename($file);
        if (!in_array($migration_name, $run_migrations)) {
            $sql = file_get_contents($file);
            if ($conn->multi_query($sql)) {
                // To clear the results of multi_query
                while ($conn->next_result()) {;}
                $stmt = $conn->prepare("INSERT INTO migrations (migration) VALUES (?)");
                $stmt->bind_param("s", $migration_name);
                $stmt->execute();
                echo "Migration successful: $migration_name\n";
            } else {
                echo "Error running migration $migration_name: " . $conn->error . "\n";
            }
        }
    }
}

run_migrations($conn);
?>
