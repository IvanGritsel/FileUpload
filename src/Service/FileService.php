<?php

namespace App\Service;

use App\Logger\Logger;
use App\Logger\LogLevel;

class FileService
{
    private array $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp'];

    private Logger $logger;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
    }

    public function getFilesSize(): int|false
    {
        $this->logger->log(LogLevel::INFO, 'Accessing size of stored files');
        if (!$this->uploadsExistOrCreate()) {
            return false;
        }

        $filesInDir = scandir(UPLOAD_DIR);
        $size = 0;
        foreach ($filesInDir as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $size += filesize(UPLOAD_DIR . '/' . $file);
        }
        $this->logger->log(LogLevel::DEBUG, "Files appear to occupy $size B of space");

        return $size * 1.4;
    }

    public function getAll(): array
    {
        $this->logger->log(LogLevel::INFO, 'Accessing all files');
        $result = [];
        if (!$this->uploadsExistOrCreate()) {
            return $result;
        }

        $filesInDir = scandir(UPLOAD_DIR);
        foreach ($filesInDir as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $reading = fopen(UPLOAD_DIR . '/' . $file, 'r');
            $result[] = [
                'name' => $file,
                'size' => filesize(UPLOAD_DIR . '/' . $file),
                'meta' => $this->readMetadata(UPLOAD_DIR . '/' . $file),
                'body' => base64_encode(fread($reading, filesize(UPLOAD_DIR . '/' . $file))),
            ];
            fclose($reading);
        }
        $this->logger->log(LogLevel::DEBUG, 'Sending back ' . sizeof($result) . ' file(s)');

        return $result;
    }

    private function readMetadata(string $path): array
    {
        $exif = @exif_read_data($path, 0, true);
        if ($exif === false) {
            return ['No metadata'];
        }
        $metadata = [];
        foreach ($exif as $key => $section) {
            foreach ($section as $name => $value) {
                $metadata[$key . $name] = $value;
            }
        }

        return $metadata;
    }

    public function add(string $fileString): bool
    {
        $this->logger->log(LogLevel::INFO, 'Creating new file');
        if (!file_exists(UPLOAD_DIR)) {
            @mkdir(UPLOAD_DIR);
        }
        $decodedFile = json_decode($fileString, true);
        $fileName = $decodedFile['name'];
        $this->logger->log(LogLevel::INFO, "Trying to create \"$fileName\" file");
        if (!file_exists(UPLOAD_DIR . '/' . $fileName)) {
            $fileBody = $decodedFile['body'];
            $fileBody = base64_decode($fileBody, true);
            $file = fopen(UPLOAD_DIR . '/' . $fileName, 'w');
            fwrite($file, $fileBody);
            fclose($file);
            $size = filesize(UPLOAD_DIR . '/' . $fileName);

            $this->logger->log(LogLevel::INFO, "\"$fileName\" successfully created. Size: $size B");

            return true;
        } else {
            $this->logger->log(LogLevel::WARNING, "\"$fileName\" already exist in uploads");

            return false;
        }
    }

    private function uploadsExistOrCreate(): bool
    {
        if (!file_exists(UPLOAD_DIR)) {
            @mkdir(UPLOAD_DIR);
            $this->logger->log(LogLevel::WARNING, 'Uploads directory was not found and was created');

            return false;
        }

        return true;
    }
}
