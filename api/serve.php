<?php
$bucketName = "satria-a49bb.firebasestorage.app";
$file = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($file)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

$firebaseUrl = "https://firebasestorage.googleapis.com/v0/b/" . $bucketName . "/o/" . urlencode($file) . "?alt=media";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $firebaseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$content = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

curl_close($ch);

if ($httpCode == 200) {
    header("Content-Type: " . $contentType);
    header("Content-Length: " . strlen($content));
    echo $content;
} else {
    http_response_code(404);
    echo "File not found.";
}
?>