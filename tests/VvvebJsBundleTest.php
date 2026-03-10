<?php

namespace Makraz\VvvebJsBundle\Tests;

use Makraz\VvvebJsBundle\VvvebJsBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class VvvebJsBundleTest extends TestCase
{
    public function testBundleExtendsSymfonyBundle(): void
    {
        $bundle = new VvvebJsBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    public function testGetPathReturnsProjectRoot(): void
    {
        $bundle = new VvvebJsBundle();
        $this->assertSame(\dirname(__DIR__), $bundle->getPath());
    }

    public function testGetPathContainsComposerJson(): void
    {
        $bundle = new VvvebJsBundle();
        $this->assertFileExists($bundle->getPath().'/composer.json');
    }
}
