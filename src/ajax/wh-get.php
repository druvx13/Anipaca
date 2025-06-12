<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

session_start();

//error_reporting(E_ALL);
//ini_set('display_errors', 0);

header('Content-Type: application/json');

// Get anime ID from query parameter

$animeId = $_GET['animeId'] ?? null;


if (!$animeId) {
    echo json_encode([
        'success' => false,
        'error' => 'Anime ID is required'
    ]);
    exit;
}

// Get user ID from cookie
$userId = $_COOKIE['userID'] ?? null;

if (!$userId) {
    echo json_encode([
        'success' => false,
        'error' => 'User ID is required'
    ]);
    exit;
}

// Check database connection - This should be handled by mysqli_report if set in _config.php
// However, keeping a basic check can be a fallback if _config.php is ever misconfigured.
if (!$conn) { // Simplified check, connect_error will be caught by mysqli_report
    error_log("Database connection object is null in wh-get.php");
    echo json_encode([
        'success' => false,
        'error' => 'Database connection error. Please try again later.'
    ]);
    exit;
}

$stmt = null;
try {
    // Query database to get watched episodes for this anime and user
    $sql = "SELECT episodes_watched FROM watched_episode WHERE anime_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // This should ideally be caught by mysqli_report if enabled
        error_log("Failed to prepare statement in wh-get.php: " . $conn->error);
        echo json_encode([
            'success' => false,
            'error' => 'Error retrieving watch history data.'
        ]);
        exit;
    }

    $stmt->bind_param('si', $animeId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $watchedEpisodes = [];
    if ($result) { // Check if result is valid
        while ($row = $result->fetch_assoc()) {
            // Assuming episodes_watched is stored as a comma-separated list
            if (!empty($row['episodes_watched'])) {
                $episodes = explode(',', $row['episodes_watched']);
                foreach ($episodes as $episode) {
                    // Add episode to the watched list
                    $watchedEpisodes[] = (int)$episode;
                }
            }
        }
    }

    echo json_encode([
        'success' => true,
        'watchedEpisodes' => array_unique($watchedEpisodes) // Ensure unique episodes
    ]);

} catch (mysqli_sql_exception $e) {
    error_log("Watch History Get Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error retrieving watch history.'
    ]);
} finally {
    if ($stmt) {
        $stmt->close();
    }
}