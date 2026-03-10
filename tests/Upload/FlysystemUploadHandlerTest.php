<?php

namespace Makraz\VvvebJsBundle\Tests\Upload;

use League\Flysystem\FilesystemOperator;
use Makraz\VvvebJsBundle\Upload\FlysystemUploadHandler;
use Makraz\VvvebJsBundle\Upload\UploadHandlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FlysystemUploadHandlerTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $handler = new FlysystemUploadHandler($filesystem, 'uploads/vvvebjs', 'https://cdn.example.com', new AsciiSlugger());

        $this->assertInstanceOf(UploadHandlerInterface::class, $handler);
    }

    public function testUploadWritesStreamToFilesystem(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->stringStartsWith('uploads/vvvebjs/'),
                $this->isType('resource'),
            );

        $handler = new FlysystemUploadHandler($filesystem, 'uploads/vvvebjs', 'https://cdn.example.com', new AsciiSlugger());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.jpg';
        file_put_contents($tempFile, 'fake image data');
        $file = new UploadedFile($tempFile, 'photo.jpg', 'image/jpeg', null, true);

        $result = $handler->upload($file);

        $this->assertStringStartsWith('https://cdn.example.com/', $result);
        $this->assertStringContainsString('photo', $result);

        @unlink($tempFile);
    }

    public function testUploadPublicUrlTrailingSlashNormalized(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->method('writeStream');

        $handler = new FlysystemUploadHandler($filesystem, 'uploads/vvvebjs', 'https://cdn.example.com/', new AsciiSlugger());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.txt';
        file_put_contents($tempFile, 'content');
        $file = new UploadedFile($tempFile, 'file.txt', 'text/plain', null, true);

        $result = $handler->upload($file);

        $afterProtocol = substr($result, \strlen('https://'));
        $this->assertStringNotContainsString('//', $afterProtocol);

        @unlink($tempFile);
    }

    public function testUploadGeneratesUniqueFilenames(): void
    {
        $paths = [];
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->method('writeStream')->willReturnCallback(static function (string $path) use (&$paths) {
            $paths[] = $path;
        });

        $handler = new FlysystemUploadHandler($filesystem, 'uploads', 'https://cdn.example.com', new AsciiSlugger());

        for ($i = 0; $i < 2; ++$i) {
            $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.txt';
            file_put_contents($tempFile, "content $i");
            $file = new UploadedFile($tempFile, 'same.txt', 'text/plain', null, true);
            $handler->upload($file);
            @unlink($tempFile);
        }

        $this->assertCount(2, $paths);
        $this->assertNotSame($paths[0], $paths[1]);
    }

    public function testUploadPathTrailingSlashNormalized(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->method('writeStream');

        $handler = new FlysystemUploadHandler($filesystem, 'uploads/vvvebjs/', 'https://cdn.example.com', new AsciiSlugger());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.txt';
        file_put_contents($tempFile, 'content');
        $file = new UploadedFile($tempFile, 'test.txt', 'text/plain', null, true);

        $result = $handler->upload($file);

        $this->assertStringNotContainsString('//', substr($result, \strlen('https://')));

        @unlink($tempFile);
    }

    public function testUploadFallsBackToBinExtension(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->method('writeStream');

        $handler = new FlysystemUploadHandler($filesystem, 'uploads', 'https://cdn.example.com', new AsciiSlugger());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        // Write truly unrecognizable binary content so guessExtension() returns null
        file_put_contents($tempFile, "\x00\x01\x02\x03\x04\x05");
        $file = new UploadedFile($tempFile, 'noext', 'application/octet-stream', null, true);

        $result = $handler->upload($file);

        $this->assertStringEndsWith('.bin', $result);

        @unlink($tempFile);
    }

    public function testUploadClosesStreamOnWriteException(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->method('writeStream')->willThrowException(new \RuntimeException('Write failed'));

        $handler = new FlysystemUploadHandler($filesystem, 'uploads', 'https://cdn.example.com', new AsciiSlugger());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.txt';
        file_put_contents($tempFile, 'content');
        $file = new UploadedFile($tempFile, 'test.txt', 'text/plain', null, true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Write failed');

        try {
            $handler->upload($file);
        } finally {
            @unlink($tempFile);
        }
    }
}
