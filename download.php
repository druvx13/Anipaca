<?php
require_once(__DIR__ . '/_config.php');

// Set reasonable time limits
set_time_limit(3600); // 1 hour
ini_set('memory_limit', '1024M'); // 1GB

// Get parameters
$rawId = $_GET['id'] ?? null;
$server = $_GET['server'] ?? 'hd-1';
$type = $_GET['type'] ?? 'sub';

if (!$rawId) {
    die("Error: Missing episode ID.");
}

// Fetch stream data from API
$ch = curl_init();
$api_url = "$zpi/stream?id={$rawId}&server={$server}&type={$type}";
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    die("Error: Failed to fetch stream data from API.");
}

$data = json_decode($response, true);
if (!$data || !$data['success'] || !isset($data['results']['streamingLink']['link']['file'])) {
    die("Error: Invalid API response or no streaming link found.");
}

$m3u8_url = $data['results']['streamingLink']['link']['file'];
$file_name = "anime_episode.mp4"; // Generic name

// --- HLS Stream Downloading and Combining ---

// Fetch the master M3U8 playlist
$master_playlist_content = file_get_contents($m3u8_url);
if ($master_playlist_content === false) {
    die("Error: Could not fetch master M3U8 playlist.");
}

// Find the highest quality stream
$lines = explode("\n", $master_playlist_content);
$highest_bandwidth = 0;
$stream_playlist_url = '';

for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], '#EXT-X-STREAM-INF') !== false) {
        preg_match('/BANDWIDTH=(\d+)/', $lines[$i], $matches);
        $bandwidth = intval($matches[1]);
        if ($bandwidth > $highest_bandwidth) {
            $highest_bandwidth = $bandwidth;
            // The stream playlist URL is the next line
            if (isset($lines[$i + 1])) {
                 // Check if the URL is relative or absolute
                if (strpos($lines[$i + 1], 'http') !== 0) {
                    $base_url = substr($m3u8_url, 0, strrpos($m3u8_url, '/') + 1);
                    $stream_playlist_url = $base_url . $lines[$i + 1];
                } else {
                    $stream_playlist_url = $lines[$i + 1];
                }
            }
        }
    }
}


// If no stream playlist found, assume the master playlist is the only one
if (empty($stream_playlist_url)) {
    $stream_playlist_url = $m3u8_url;
}


// Fetch the stream playlist
$stream_playlist_content = file_get_contents($stream_playlist_url);
if ($stream_playlist_content === false) {
    die("Error: Could not fetch stream M3U8 playlist.");
}

// Create a temporary file to store the combined video
$temp_file = tmpfile();
if ($temp_file === false) {
    die("Error: Could not create temporary file.");
}

// Download and append each segment
$segment_lines = explode("\n", $stream_playlist_content);
$segment_base_url = substr($stream_playlist_url, 0, strrpos($stream_playlist_url, '/') + 1);

foreach ($segment_lines as $line) {
    if (!empty($line) && $line[0] !== '#') {
        $segment_url = $line;
        // Check if the segment URL is relative or absolute
        if (strpos($segment_url, 'http') !== 0) {
            $segment_url = $segment_base_url . $segment_url;
        }

        $segment_content = file_get_contents($segment_url);
        if ($segment_content !== false) {
            fwrite($temp_file, $segment_content);
        } else {
            error_log("Failed to download segment: $segment_url");
        }
    }
}

// Get the size of the temporary file
$file_size = ftell($temp_file);
fseek($temp_file, 0);

// Send headers to the browser
header('Content-Description: File Transfer');
header('Content-Type: video/mp4');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $file_size);

// Stream the file to the browser
fpassthru($temp_file);

// Close and delete the temporary file
fclose($temp_file);

exit;
?>
