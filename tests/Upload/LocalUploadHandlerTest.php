<?php

namespace Makraz\VvvebJsBundle\Tests\Upload;

use Makraz\VvvebJsBundle\Upload\LocalUploadHandler;
use Makraz\VvvebJsBundle\Upload\UploadHandlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class LocalUploadHandlerTest extends TestCase
{
    private string $uploadDir;

    protected function setUp(): void
    {
        $this->uploadDir = sys_get_temp_dir().'/vvvebjs_test_'.bin2hex(random_bytes(4));
        mkdir($this->uploadDir, 0o777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->uploadDir);
    }

    public function testImplementsInterface(): void
    {
        $handler = new LocalUploadHandler($this->uploadDir, '/uploads', new AsciiSlugger());
        $this->assertInstanceOf(UploadHandlerInterface::class, $handler);
    }

    public function testUploadMovesFileAndReturnsPublicPath(): void
    {
        $handler = new LocalUploadHandler($this->uploadDir, '/uploads/vvvebjs', new AsciiSlugger());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.txt';
        file_put_contents($tempFile, 'test content');

        $file = new UploadedFile($tempFile, 'document.txt', 'text/plain', null, true);
        $result = $handler->upload($file);

        $this->assertStringStartsWith('/uploads/vvvebjs/', $result);
        $this->assertStringContainsString('document', $result);

        $filename = basename($result);
        $this->assertFileExists($this->uploadDir.'/'.$filename);
    }

    public function testUploadGeneratesUniqueFilenames(): void
    {
        $handler = new LocalUploadHandler($this->uploadDir, '/uploads', new AsciiSlugger());

        $tempFile1 = tempnam(sys_get_temp_dir(), 'test_').'.txt';
        file_put_contents($tempFile1, 'content 1');
        $file1 = new UploadedFile($tempFile1, 'same.txt', 'text/plain', null, true);

        $tempFile2 = tempnam(sys_get_temp_dir(), 'test_').'.txt';
        file_put_contents($tempFile2, 'content 2');
        $file2 = new UploadedFile($tempFile2, 'same.txt', 'text/plain', null, true);

        $result1 = $handler->upload($file1);
        $result2 = $handler->upload($file2);

        $this->assertNotSame($result1, $result2);
    }

    public function testPublicPathTrailingSlashIsNormalized(): void
    {
        $handler = new LocalUploadHandler($this->uploadDir, '/uploads/vvvebjs/', new AsciiSlugger());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.txt';
        file_put_contents($tempFile, 'test');

        $file = new UploadedFile($tempFile, 'test.txt', 'text/plain', null, true);
        $result = $handler->upload($file);

        $this->assertStringNotContainsString('//', substr($result, 1));
    }

    public function testUploadFallsBackToBinExtension(): void
    {
        $handler = new LocalUploadHandler($this->uploadDir, '/uploads', new AsciiSlugger());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        // Write truly unrecognizable binary content so guessExtension() returns null
        file_put_contents($tempFile, "\x00\x01\x02\x03\x04\x05");

        $file = new UploadedFile($tempFile, 'noext', 'application/octet-stream', null, true);
        $result = $handler->upload($file);

        // guessExtension() returns null → fallback to 'bin'
        $this->assertStringEndsWith('.bin', $result);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }
            $path = $dir.'/'.$item;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }
}
