<?php

namespace Makraz\VvvebJsBundle\Tests\DTO\Enums;

use Makraz\VvvebJsBundle\DTO\Enums\VvvebJsPlugin;
use PHPUnit\Framework\TestCase;

class VvvebJsPluginTest extends TestCase
{
    public function testAllCasesExist(): void
    {
        $this->assertCount(6, VvvebJsPlugin::cases());
    }

    /**
     * @dataProvider pluginValuesProvider
     */
    public function testPluginValues(VvvebJsPlugin $plugin, string $expected): void
    {
        $this->assertSame($expected, $plugin->value);
    }

    public static function pluginValuesProvider(): array
    {
        return [
            [VvvebJsPlugin::GOOGLE_FONTS, 'google-fonts'],
            [VvvebJsPlugin::CODE_MIRROR, 'codemirror'],
            [VvvebJsPlugin::JSZIP, 'jszip'],
            [VvvebJsPlugin::AOS, 'aos'],
            [VvvebJsPlugin::AI_ASSISTANT, 'ai-assistant'],
            [VvvebJsPlugin::MEDIA, 'media'],
        ];
    }

    public function testFromValidValue(): void
    {
        $this->assertSame(VvvebJsPlugin::CODE_MIRROR, VvvebJsPlugin::from('codemirror'));
    }

    public function testTryFromInvalidValue(): void
    {
        $this->assertNull(VvvebJsPlugin::tryFrom('nonexistent'));
    }
}
