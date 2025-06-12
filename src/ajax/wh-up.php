<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1); // Keep for development, consider turning off in production
header('Content-Type: application/json');

// Validate database connection - this initial check is okay,
// but subsequent errors should be caught by mysqli_report if _config.php sets it.
if (!$conn) { // Simpler check, as mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT) handles connect_error
    http_response_code(500);
    // Log this specific failure point if it happens despite mysqli_report settings
    error_log("wh-up.php: Database connection object is null BEFORE transaction.");
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
    exit();
}

// Validate and sanitize input data
$data = json_decode(file_get_contents('php://input'), true);

$userId = filter_var($_COOKIE['userID'] ?? null, FILTER_VALIDATE_INT);
$animeId = isset($data['animeId']) ? htmlspecialchars(trim($data['animeId']), ENT_QUOTES) : null;
$animeName = isset($data['animeName']) ? htmlspecialchars($data['animeName'], ENT_QUOTES) : null;
$poster = filter_var($data['poster'] ?? null, FILTER_SANITIZE_URL);
$subCount = filter_var($data['subCount'] ?? null, FILTER_VALIDATE_INT);
$dubCount = filter_var($data['dubCount'] ?? null, FILTER_VALIDATE_INT);
$episodeNumber = filter_var($data['episodeNumber'] ?? null, FILTER_VALIDATE_INT);

if ($userId === null || $animeId === null || !$animeName || $episodeNumber === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid required data']);
    exit();
}

$conn->begin_transaction();
$stmtUpdateWatchHistory = null;
$stmtUpdateWatchedEpisodes = null;

try {
    // 1. Update watch_history table
    $sqlUpdateWatchHistory = "
        INSERT INTO watch_history
        (user_id, anime_id, anime_name, poster, sub_count, dub_count, episode_number, created_at, updated_at)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
        episode_number = VALUES(episode_number),
        poster = VALUES(poster),
        sub_count = VALUES(sub_count),
        dub_count = VALUES(dub_count),
        updated_at = NOW()";

    $stmtUpdateWatchHistory = $conn->prepare($sqlUpdateWatchHistory);
    if (!$stmtUpdateWatchHistory) {
        // If prepare fails, it will throw an exception if mysqli_report is configured for errors.
        throw new mysqli_sql_exception("Failed to prepare statement for watch_history: " . $conn->error);
    }

    $stmtUpdateWatchHistory->bind_param(
        'isssiii',
        $userId, $animeId, $animeName, $poster, $subCount, $dubCount, $episodeNumber
    );

    if (!$stmtUpdateWatchHistory->execute()) {
        // If execute fails, it will throw an exception if mysqli_report is configured.
        throw new mysqli_sql_exception("Error executing watch_history statement: " . $stmtUpdateWatchHistory->error);
    }

    // 2. Update watched_episode table
    $sqlUpdateWatchedEpisodes = "
        INSERT INTO watched_episode
        (user_id, anime_id, episodes_watched, updated_at)
        VALUES
        (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        episodes_watched = IF(
            FIND_IN_SET(?, episodes_watched) > 0,
            episodes_watched,
            CONCAT(IFNULL(episodes_watched, ''), IF(episodes_watched IS NULL OR episodes_watched = '', '', ','), ?)
        ),
        updated_at = NOW()";

    $stmtUpdateWatchedEpisodes = $conn->prepare($sqlUpdateWatchedEpisodes);
    if (!$stmtUpdateWatchedEpisodes) {
        throw new mysqli_sql_exception("Failed to prepare statement for watched_episode: " . $conn->error);
    }

    $episodeStr = (string)$episodeNumber;
    $stmtUpdateWatchedEpisodes->bind_param(
        'issss',
        $userId, $animeId, $episodeStr, $episodeStr, $episodeStr
    );

    if (!$stmtUpdateWatchedEpisodes->execute()) {
        throw new mysqli_sql_exception("Error executing watched_episode statement: " . $stmtUpdateWatchedEpisodes->error);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true]);

} catch (mysqli_sql_exception $e) {
    // Rollback transaction on DB error
    if ($conn->server_status & MYSQLI_TRANS_IN_PROGRESS) { // Check if transaction is active
       $conn->rollback();
    }
    error_log("Watch History Update DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating watch history']);
} catch (Exception $e) { // Catch other non-DB exceptions
    if ($conn->server_status & MYSQLI_TRANS_IN_PROGRESS) {
       $conn->rollback();
    }
    error_log("Watch History Update Non-DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A non-database error occurred while updating watch history']);
} finally {
    if ($stmtUpdateWatchHistory instanceof mysqli_stmt) {
        $stmtUpdateWatchHistory->close();
    }
    if ($stmtUpdateWatchedEpisodes instanceof mysqli_stmt) {
        $stmtUpdateWatchedEpisodes->close();
    }
    if ($conn) {
        $conn->close();
    }
}
?>
