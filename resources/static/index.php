<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new Twig\Environment($loader);

$sock = socket_create(AF_INET, SOCK_STREAM, 0);
if (!$sock) {
    echo $twig->render('error_page.twig', [
        'errorCode' => 503,
    ]);
    die();
}

$result = socket_connect($sock, '127.0.0.1', 9090);
if (!$result) {
    echo $twig->render('error_page.twig', [
        'errorCode' => 503,
    ]);
    die();
}

$message = "GET /files/all/size HTTP1.1\r\n\r\n";
socket_write($sock, $message, strlen($message));

$filesSize = socket_read($sock, 1024);
socket_close($sock);

$sizeResponseLines = preg_split("/\r\n/", $filesSize);
$filesSize = end($sizeResponseLines);

$json = '';
if ($filesSize != 'false') {
    //echo $filesSize;
    $sock = socket_create(AF_INET, SOCK_STREAM, 0);
    $result = socket_connect($sock, '127.0.0.1', 9090);

    $message = "GET /files/all HTTP1.1\r\n\r\n";
    socket_write($sock, $message, strlen($message));

    $rawData = socket_read($sock, $filesSize + 1000);
    $responseLines = preg_split("/\r\n/", $rawData);
    $json = $responseLines[sizeof($responseLines) - 1];
    socket_close($sock);
}

if ($json) {
    //echo $json;
    $json = json_decode($json, true);
    //echo json_last_error_msg();
    $imageExtensions = ['png', 'jpeg', 'gif', 'bmp'];
    foreach ($json as $file) {
        $fileName = $file['name'];
        $array = preg_split('/./', $fileName);
        $file['body'] = base64_decode($file['body']);
    }
}
$url = $_SERVER['REQUEST_URI'];
$error = parse_url($url)['query'];

//echo $error;
if ($error) {
    $arr = preg_split("/\=/", $error);
    $params = [$arr[0] => $arr[1]];
}

//parse_str($url['query'], $params);

if (!empty($params)) {
    if ($params['error']) {
        //echo $params['error'];
        echo $twig->render('main_page.twig', [
            'pageTitle' => 'File Upload',
            'files' => $json,
            'error' => $params['error'],
        ]);
    }
} else {
    echo $twig->render('main_page.twig', [
        'pageTitle' => 'File Upload',
        'files' => $json,
    ]);
}
