<?php

namespace Makraz\VvvebJsBundle\Tests\Controller;

use Makraz\VvvebJsBundle\Controller\VvvebJsUploadController;
use Makraz\VvvebJsBundle\Upload\UploadHandlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class VvvebJsUploadControllerTest extends TestCase
{
    public function testUploadSuccess(): void
    {
        $handler = $this->createMock(UploadHandlerInterface::class);
        $handler->method('upload')->willReturn('/uploads/vvvebjs/image-abc123.jpg');

        $controller = new VvvebJsUploadController($handler, 10 * 1024 * 1024, ['image/jpeg']);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.jpg';
        file_put_contents($tempFile, "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9");

        $file = new UploadedFile($tempFile, 'photo.jpg', 'image/jpeg', null, true);

        $request = new Request(files: ['file' => $file]);
        $response = $controller->upload($request);

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertSame('/uploads/vvvebjs/image-abc123.jpg', $data['url']);

        @unlink($tempFile);
    }

    public function testUploadNoFile(): void
    {
        $handler = $this->createMock(UploadHandlerInterface::class);
        $controller = new VvvebJsUploadController($handler, 10 * 1024 * 1024, []);

        $request = new Request();
        $response = $controller->upload($request);

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame('No file uploaded.', $data['message']);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testUploadInvalidFile(): void
    {
        $handler = $this->createMock(UploadHandlerInterface::class);
        $controller = new VvvebJsUploadController($handler, 10 * 1024 * 1024, []);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'data');

        $file = new UploadedFile($tempFile, 'test.txt', 'text/plain', \UPLOAD_ERR_PARTIAL);

        $request = new Request(files: ['file' => $file]);
        $response = $controller->upload($request);

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame(400, $response->getStatusCode());

        @unlink($tempFile);
    }

    public function testUploadTooLarge(): void
    {
        $handler = $this->createMock(UploadHandlerInterface::class);
        $controller = new VvvebJsUploadController($handler, 10, ['image/jpeg']);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, str_repeat('x', 100));

        $file = new UploadedFile($tempFile, 'large.jpg', 'image/jpeg', null, true);

        $request = new Request(files: ['file' => $file]);
        $response = $controller->upload($request);

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('File too large', $data['message']);

        @unlink($tempFile);
    }

    public function testUploadDisallowedMimeType(): void
    {
        $handler = $this->createMock(UploadHandlerInterface::class);
        $controller = new VvvebJsUploadController($handler, 10 * 1024 * 1024, ['image/jpeg']);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'not an image');

        $file = new UploadedFile($tempFile, 'test.txt', 'text/plain', null, true);

        $request = new Request(files: ['file' => $file]);
        $response = $controller->upload($request);

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('not allowed', $data['message']);

        @unlink($tempFile);
    }

    public function testUploadEmptyMimeTypesAcceptsAll(): void
    {
        $handler = $this->createMock(UploadHandlerInterface::class);
        $handler->method('upload')->willReturn('/uploads/file.txt');

        $controller = new VvvebJsUploadController($handler, 10 * 1024 * 1024, []);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'text');

        $file = new UploadedFile($tempFile, 'test.txt', 'text/plain', null, true);

        $request = new Request(files: ['file' => $file]);
        $response = $controller->upload($request);

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);

        @unlink($tempFile);
    }

    public function testUploadHandlerException(): void
    {
        $handler = $this->createMock(UploadHandlerInterface::class);
        $handler->method('upload')->willThrowException(new \RuntimeException('Disk full'));

        $controller = new VvvebJsUploadController($handler, 10 * 1024 * 1024, []);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.jpg';
        file_put_contents($tempFile, "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9");

        $file = new UploadedFile($tempFile, 'test.jpg', 'image/jpeg', null, true);

        $request = new Request(files: ['file' => $file]);
        $response = $controller->upload($request);

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame('Disk full', $data['message']);
        $this->assertSame(400, $response->getStatusCode());

        @unlink($tempFile);
    }
}
