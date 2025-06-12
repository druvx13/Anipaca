<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
header('Content-Type: application/json');

// Suppress direct error output, rely on logging for AJAX
ini_set('display_errors', '0');
error_reporting(0);

$episodeId = $_GET['episodeId'] ?? null; // This is the $rawId like in player files (e.g., animeid?ep=1)
$serverName = $_GET['serverName'] ?? null;
$serverType = $_GET['serverType'] ?? 'sub'; // Default to sub

if (!$episodeId || !$serverName) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters (episodeId or serverName).']);
    exit;
}

// Logic adapted from player/sub.php or player/dub.php to fetch stream link
$data = null;
$ch = null; // Initialize $ch

try {
    $ch = curl_init();
    // Ensure $zpi is available from _config.php
    if (empty($zpi)) {
        throw new Exception("ZPI API base URL is not configured.");
    }
    $api_url = "$zpi/stream?id=" . urlencode($episodeId) . "&server=" . urlencode($serverName) . "&type=" . urlencode($serverType);

    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Added timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Common for dev, consider security implications
    // Add any other necessary cURL options from player files if needed

    $response = curl_exec($ch);
    $curl_error_num = curl_errno($ch);
    $curl_error_msg = curl_error($ch);

    if ($curl_error_num || $response === false) {
        throw new Exception("cURL error ($curl_error_num) for $api_url: $curl_error_msg");
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON decode error: " . json_last_error_msg() . ". Response: " . substr($response, 0, 200));
    }

    if ($data && isset($data['success']) && $data['success'] === true && isset($data['results']['streamingLink']['link']['file'])) {
        $m3u8_file_url = $data['results']['streamingLink']['link']['file'];

        // Ensure $proxy is available from _config.php
        if (empty($proxy)) {
            throw new Exception("Proxy URL is not configured.");
        }
        // Construct the proxied M3U8 URL carefully based on player files
        $final_m3u8_url = $proxy . $m3u8_file_url . "&headers=" . urlencode(json_encode(["Referer" => "https://megacloud.club/"]));

        echo json_encode(['success' => true, 'm3u8_url' => $final_m3u8_url, 'serverType' => $serverType]);
    } else {
        throw new Exception("API success false or link not found for $api_url. Response: " . substr($response, 0, 200));
    }

} catch (Exception $e) {
    error_log("get_m3u8_link.php: Exception: " . $e->getMessage());
    if (!headers_sent()) {
        http_response_code(500); // Internal Server Error
    }
    echo json_encode(['success' => false, 'error' => 'Failed to retrieve stream link. Please try again later.']);
} finally {
    if (is_resource($ch)) { // Check if $ch is a cURL handle
        curl_close($ch);
    }
}
exit; // Ensure script terminates after JSON output
?>
