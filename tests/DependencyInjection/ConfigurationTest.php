<?php

namespace Makraz\VvvebJsBundle\Tests\DependencyInjection;

use Makraz\VvvebJsBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $config = $this->processConfiguration([]);

        $this->assertSame('https://cdn.jsdelivr.net/gh/givanz/VvvebJs@master', $config['cdn_url']);
        $this->assertFalse($config['upload']['enabled']);
        $this->assertSame('local', $config['upload']['handler']);
        $this->assertStringContainsString('uploads/vvvebjs', $config['upload']['local_dir']);
        $this->assertSame('/uploads/vvvebjs', $config['upload']['local_public_path']);
        $this->assertNull($config['upload']['flysystem_storage']);
        $this->assertSame('uploads/vvvebjs', $config['upload']['flysystem_path']);
        $this->assertSame('', $config['upload']['flysystem_public_url']);
        $this->assertNull($config['upload']['custom_handler']);
        $this->assertSame(10 * 1024 * 1024, $config['upload']['max_file_size']);
        $this->assertSame(
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'video/mp4', 'video/webm', 'application/pdf'],
            $config['upload']['allowed_mime_types'],
        );
    }

    public function testCustomCdnUrl(): void
    {
        $config = $this->processConfiguration([
            'cdn_url' => '/local/vvvebjs',
        ]);

        $this->assertSame('/local/vvvebjs', $config['cdn_url']);
    }

    public function testUploadEnabled(): void
    {
        $config = $this->processConfiguration([
            'upload' => ['enabled' => true],
        ]);

        $this->assertTrue($config['upload']['enabled']);
    }

    public function testHandlerEnum(): void
    {
        foreach (['local', 'flysystem', 'custom'] as $handler) {
            $config = $this->processConfiguration([
                'upload' => ['handler' => $handler],
            ]);

            $this->assertSame($handler, $config['upload']['handler']);
        }
    }

    public function testInvalidHandlerThrows(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

        $this->processConfiguration([
            'upload' => ['handler' => 'invalid'],
        ]);
    }

    public function testCustomMaxFileSize(): void
    {
        $config = $this->processConfiguration([
            'upload' => ['max_file_size' => 20 * 1024 * 1024],
        ]);

        $this->assertSame(20 * 1024 * 1024, $config['upload']['max_file_size']);
    }

    public function testCustomAllowedMimeTypes(): void
    {
        $config = $this->processConfiguration([
            'upload' => ['allowed_mime_types' => ['application/pdf']],
        ]);

        $this->assertSame(['application/pdf'], $config['upload']['allowed_mime_types']);
    }

    public function testFlysystemConfig(): void
    {
        $config = $this->processConfiguration([
            'upload' => [
                'handler' => 'flysystem',
                'flysystem_storage' => 'default.storage',
                'flysystem_path' => 'media/vvvebjs',
                'flysystem_public_url' => 'https://cdn.example.com',
            ],
        ]);

        $this->assertSame('default.storage', $config['upload']['flysystem_storage']);
        $this->assertSame('media/vvvebjs', $config['upload']['flysystem_path']);
        $this->assertSame('https://cdn.example.com', $config['upload']['flysystem_public_url']);
    }

    public function testCustomHandlerConfig(): void
    {
        $config = $this->processConfiguration([
            'upload' => [
                'handler' => 'custom',
                'custom_handler' => 'app.my_upload_handler',
            ],
        ]);

        $this->assertSame('app.my_upload_handler', $config['upload']['custom_handler']);
    }

    private function processConfiguration(array $config): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), ['vvvebjs' => $config]);
    }
}
