<?php

namespace App\Listener;

use App\Dispatcher\Dispatcher;

class Listener
{
    private string $PATH_TO_CONFIG = ROOT_DIR . 'config/web_config.json';
    private string $HOST;
    private int $PORT;

    private Dispatcher $dispatcher;

    public function __construct()
    {
        $fileContents = json_decode(file_get_contents($this->PATH_TO_CONFIG), true);
        $this->HOST = $fileContents['host'];
        $this->PORT = $fileContents['port'];

        $this->dispatcher = new Dispatcher();
    }

    public function startListening(): void
    {
        set_time_limit(0);

        $sock = socket_create(AF_INET, SOCK_STREAM, 0) or die('Unable to create socket');

        $result = socket_bind($sock, $this->HOST, $this->PORT) or die('Unable to bind socket');
        echo "Listening to $this->HOST:$this->PORT\r\n";
        while (true) {
            $result = socket_listen($sock, 3) or die('Im dead');
            $spawn = socket_accept($sock) or die('Im dead');
            $input = socket_read($spawn, 2097152) or die('Im dead');

            echo "Received following: \r\n$input\r\n";

            $response = $this->dispatcher->dispatch($input);

            echo "\r\nSending back this: \r\n$response\r\n";

            socket_write($spawn, $response, strlen($response));
        }
    }
}
