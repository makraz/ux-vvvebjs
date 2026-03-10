<?php

namespace Makraz\VvvebJsBundle\Tests\Form;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Makraz\VvvebJsBundle\Form\VvvebJsAdminField;
use Makraz\VvvebJsBundle\Form\VvvebJsType;
use PHPUnit\Framework\TestCase;

class VvvebJsAdminFieldTest extends TestCase
{
    public function testImplementsFieldInterface(): void
    {
        $field = VvvebJsAdminField::new('content');
        $this->assertInstanceOf(FieldInterface::class, $field);
    }

    public function testFieldProperty(): void
    {
        $field = VvvebJsAdminField::new('body');
        $this->assertSame('body', $field->getAsDto()->getProperty());
    }

    public function testFieldLabel(): void
    {
        $field = VvvebJsAdminField::new('content', 'Page Content');
        $this->assertSame('Page Content', $field->getAsDto()->getLabel());
    }

    public function testFieldLabelNull(): void
    {
        $field = VvvebJsAdminField::new('content');
        $this->assertNull($field->getAsDto()->getLabel());
    }

    public function testFieldLabelFalse(): void
    {
        $field = VvvebJsAdminField::new('content', false);
        $this->assertFalse($field->getAsDto()->getLabel());
    }

    public function testFieldFormType(): void
    {
        $field = VvvebJsAdminField::new('content');
        $this->assertSame(VvvebJsType::class, $field->getAsDto()->getFormType());
    }
}
