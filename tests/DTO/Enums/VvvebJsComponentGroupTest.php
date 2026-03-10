<?php

namespace Makraz\VvvebJsBundle\Tests\DTO\Enums;

use Makraz\VvvebJsBundle\DTO\Enums\VvvebJsComponentGroup;
use PHPUnit\Framework\TestCase;

class VvvebJsComponentGroupTest extends TestCase
{
    public function testAllCasesExist(): void
    {
        $this->assertCount(6, VvvebJsComponentGroup::cases());
    }

    /**
     * @dataProvider componentGroupValuesProvider
     */
    public function testComponentGroupValues(VvvebJsComponentGroup $group, string $expected): void
    {
        $this->assertSame($expected, $group->value);
    }

    public static function componentGroupValuesProvider(): array
    {
        return [
            [VvvebJsComponentGroup::COMMON, 'common'],
            [VvvebJsComponentGroup::HTML, 'html'],
            [VvvebJsComponentGroup::ELEMENTS, 'elements'],
            [VvvebJsComponentGroup::BOOTSTRAP5, 'bootstrap5'],
            [VvvebJsComponentGroup::WIDGETS, 'widgets'],
            [VvvebJsComponentGroup::EMBEDS, 'embeds'],
        ];
    }

    public function testFromValidValue(): void
    {
        $this->assertSame(VvvebJsComponentGroup::BOOTSTRAP5, VvvebJsComponentGroup::from('bootstrap5'));
    }

    public function testTryFromInvalidValue(): void
    {
        $this->assertNull(VvvebJsComponentGroup::tryFrom('nonexistent'));
    }
}
