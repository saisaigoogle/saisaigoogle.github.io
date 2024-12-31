<?php
// upload.php
header('Content-Type: application/json');

if (isset($_FILES['file'])) {
    $ch = curl_init('https://magiccube-gateway.3vjia.com/mj-biz-c-web/oss/upload/image');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'file' => new CURLFile(
            $_FILES['file']['tmp_name'],
            $_FILES['file']['type'],
            $_FILES['file']['name']
        )
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    echo $response;
} else {
    echo json_encode(['error' => '没有收到文件']);
}
?>
