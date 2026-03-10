<?php

namespace League\Flysystem;

interface FilesystemReader
{
    public function read(string $location): string;

    /**
     * @return resource
     */
    public function readStream(string $location);
}

interface FilesystemWriter
{
    public function write(string $location, string $contents, array $config = []): void;

    /**
     * @param resource $contents
     */
    public function writeStream(string $location, $contents, array $config = []): void;
}

interface FilesystemOperator extends FilesystemReader, FilesystemWriter
{
}
