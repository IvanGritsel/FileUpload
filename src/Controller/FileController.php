<?php

namespace App\Controller;

use App\Annotation\PathVariable;
use App\Annotation\RequestBodyVariable;
use App\Annotation\RequestMapping;
use App\Service\FileService;

class FileController
{
    private FileService $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    /**
     * @return string
     *
     * @RequestMapping(method="GET", path="/files/all/size")
     */
    public function getFilesSize(): string
    {
        return json_encode($this->fileService->getFilesSize());
    }

    /**
     * @return string
     *
     * @RequestMapping(method="GET", path="/files/all")
     */
    public function getAll(): string
    {
        return json_encode($this->fileService->getAll());
    }

    /**
     * @param string $stringFile
     *
     * @return string
     *
     * @RequestMapping(method="POST", path="/files/new")
     * @RequestBodyVariable(variableName="stringFile")
     */
    public function newFile(string $stringFile): string
    {
        return json_encode($this->fileService->add($stringFile));
    }
}
