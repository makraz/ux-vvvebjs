<?php

namespace Makraz\VvvebJsBundle\Upload;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class FlysystemUploadHandler implements UploadHandlerInterface
{
    public function __construct(
        private readonly FilesystemOperator $filesystem,
        private readonly string $uploadPath,
        private readonly string $publicUrlPrefix,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $filename = $this->generateFilename($file->getClientOriginalName(), $file->guessExtension() ?? 'bin');
        $path = rtrim($this->uploadPath, '/').'/'.$filename;

        $stream = fopen($file->getPathname(), 'r');
        if (false === $stream) {
            throw new \RuntimeException(\sprintf('Could not open file: %s', $file->getPathname())); // @codeCoverageIgnore
        }

        try {
            $this->filesystem->writeStream($path, $stream);
        } finally {
            if (\is_resource($stream)) {
                fclose($stream);
            }
        }

        return rtrim($this->publicUrlPrefix, '/').'/'.$path;
    }

    private function generateFilename(string $originalName, string $extension): string
    {
        $safeName = $this->slugger->slug(pathinfo($originalName, \PATHINFO_FILENAME));

        return \sprintf('%s-%s.%s', $safeName, bin2hex(random_bytes(8)), $extension);
    }
}
