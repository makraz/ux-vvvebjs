<?php

namespace Makraz\VvvebJsBundle\Upload;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadHandlerInterface
{
    /**
     * Handles an uploaded file and returns the public URL.
     */
    public function upload(UploadedFile $file): string;
}
