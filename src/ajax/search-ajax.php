<?php
require_once('../../_config.php');
header('Content-Type: application/json');

if (isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']); // DO NOT alter the keyword here
    $cacheKey = md5($keyword);
    $cachePath = __DIR__ . '/../../cache/search/';
    $cacheFile = $cachePath . $cacheKey . '.json';
    $cacheTime = 300;

    if (!is_dir($cachePath)) {
        mkdir($cachePath, 0777, true);
    }

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        echo file_get_contents($cacheFile);
        exit;
    }

    $apiUrl = "$zpi/search?keyword=" . urlencode($keyword); // Use the full URL here

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            // Consider closing $ch here if an error occurs before normal script end
            // curl_close($ch);
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        // curl_close($ch); // Not strictly necessary

        $data = json_decode($response, true);

        if ($data && isset($data['success']) && $data['success']) {
            file_put_contents($cacheFile, $response);
            echo $response;
        } else {
            $errorResponse = json_encode([
                'success' => false,
                'message' => 'No results found'
            ]);
            file_put_contents($cacheFile, $errorResponse);
            echo $errorResponse;
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No keyword provided'
    ]);
}
