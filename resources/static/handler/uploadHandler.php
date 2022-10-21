<?php
if (isset($_FILES['file_input'])) {
    //var_dump($_FILES);
    $file = $_FILES['file_input'];
    $error = '';

    $fName = $file['name'];
    $fSize = $file['size'];
    $fTemp = $file['tmp_name'];
    $fType = $file['type'];

    $sepName = explode('.', $fName);
    $ext = strtolower(end($sepName));

    $imageExtensions = ['png', 'gif', 'bmp', 'jpeg', 'jpg'];

    if (!in_array($ext, $imageExtensions) && $ext != 'txt') {
        $error = 'format';
    }

    if ($fSize > 2097152 || $fSize == 0) {
        $error = 'size';
    }
//    echo $fSize;
    if ($error == '') {
        $sock = socket_create(AF_INET, SOCK_STREAM, 0);

        $result = socket_connect($sock, '127.0.0.1', 9090);

        $toRead = fopen($fTemp, 'r');
        $body = fread($toRead, filesize($fTemp));

        $body = base64_encode($body);

        $message = "POST /files/new HTTP1.1\r\n\r\n" . "{\"name\": \"$fName\", \"body\": \"$body\"}";
        socket_write($sock, $message, strlen($message));
        $response = socket_read($sock, 1024);

        $sizeResponseLines = preg_split("/\r\n/", $response);
        $responseSuccess = end($sizeResponseLines);
        //echo $message;
        socket_close($sock);

        if ($responseSuccess == 'true') {
            header('Location: http://localhost:8080/index.php');
        } else {
            header('Location: http://localhost:8080/index.php?error=alreadyExist');
        }
    } else {
        header("Location: http://localhost:8080/index.php?error=$error");
    }
}
