<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$bucketName = "satria-a49bb.firebasestorage.app";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "error" => "File upload error"]);
        exit;
    }

    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];
    $fileType = $file['type'];
    $fileSize = $file['size'];

    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    
    $randomStr = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
    $finalName = $randomStr . "." . $extension;

    $firebaseUrl = "https://firebasestorage.googleapis.com/v0/b/" . $bucketName . "/o?name=" . urlencode($finalName);

    $fileContent = file_get_contents($fileTmpPath);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $firebaseUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: " . $fileType
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $host = $_SERVER['HTTP_HOST'];
        
        $myDomainUrl = $protocol . "://" . $host . "/" . $finalName;

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $fileSize > 0 ? floor(log($fileSize, 1024)) : 0;
        $formattedSize = number_format($fileSize / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];

        echo json_encode([
            "success" => true,
            "url" => $myDomainUrl,
            "size" => $formattedSize,
            "name" => $finalName,
            "type" => $fileType
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Upload failed"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "No file provided"]);
}
?>