<?php

namespace Makraz\VvvebJsBundle\Upload;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class LocalUploadHandler implements UploadHandlerInterface
{
    public function __construct(
        private readonly string $uploadDir,
        private readonly string $publicPath,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $filename = $this->generateFilename($file->getClientOriginalName(), $file->guessExtension() ?? 'bin');

        $file->move($this->uploadDir, $filename);

        return rtrim($this->publicPath, '/').'/'.$filename;
    }

    private function generateFilename(string $originalName, string $extension): string
    {
        $safeName = $this->slugger->slug(pathinfo($originalName, \PATHINFO_FILENAME));

        return \sprintf('%s-%s.%s', $safeName, bin2hex(random_bytes(8)), $extension);
    }
}
