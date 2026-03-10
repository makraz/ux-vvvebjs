<?php

namespace Makraz\VvvebJsBundle\Tests\Form;

use Makraz\VvvebJsBundle\DTO\Enums\VvvebJsComponentGroup;
use Makraz\VvvebJsBundle\DTO\Enums\VvvebJsPlugin;
use Makraz\VvvebJsBundle\Form\VvvebJsType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class VvvebJsTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([new VvvebJsType('https://cdn.example.com/vvvebjs')], []),
        ];
    }

    public function testSubmitValidData(): void
    {
        $htmlData = '<html><body><h1>Hello</h1></body></html>';

        $form = $this->factory->create(VvvebJsType::class);
        $form->submit($htmlData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame($htmlData, $form->getData());
    }

    public function testDefaultComponents(): void
    {
        $form = $this->factory->create(VvvebJsType::class);
        $view = $form->createView();

        $components = json_decode($view->vars['attr']['data-components'], true);

        $this->assertContains('common', $components);
        $this->assertContains('html', $components);
        $this->assertContains('elements', $components);
        $this->assertContains('bootstrap5', $components);
        $this->assertNotContains('widgets', $components);
        $this->assertNotContains('embeds', $components);
    }

    public function testCustomComponentsWithEnum(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_components' => [
                VvvebJsComponentGroup::HTML,
                VvvebJsComponentGroup::WIDGETS,
            ],
        ]);
        $view = $form->createView();

        $components = json_decode($view->vars['attr']['data-components'], true);

        $this->assertCount(2, $components);
        $this->assertContains('html', $components);
        $this->assertContains('widgets', $components);
    }

    public function testCustomComponentsWithString(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_components' => ['custom-group'],
        ]);
        $view = $form->createView();

        $components = json_decode($view->vars['attr']['data-components'], true);
        $this->assertContains('custom-group', $components);
    }

    public function testDefaultPlugins(): void
    {
        $form = $this->factory->create(VvvebJsType::class);
        $view = $form->createView();

        $plugins = json_decode($view->vars['attr']['data-plugins'], true);

        $this->assertCount(1, $plugins);
        $this->assertContains('codemirror', $plugins);
    }

    public function testCustomPluginsWithEnum(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_plugins' => [
                VvvebJsPlugin::GOOGLE_FONTS,
                VvvebJsPlugin::JSZIP,
                VvvebJsPlugin::MEDIA,
            ],
        ]);
        $view = $form->createView();

        $plugins = json_decode($view->vars['attr']['data-plugins'], true);

        $this->assertCount(3, $plugins);
        $this->assertContains('google-fonts', $plugins);
        $this->assertContains('jszip', $plugins);
        $this->assertContains('media', $plugins);
    }

    public function testCustomPluginsWithString(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_plugins' => ['my-plugin'],
        ]);
        $view = $form->createView();

        $plugins = json_decode($view->vars['attr']['data-plugins'], true);
        $this->assertContains('my-plugin', $plugins);
    }

    public function testEmptyComponents(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_components' => [],
        ]);
        $view = $form->createView();

        $components = json_decode($view->vars['attr']['data-components'], true);
        $this->assertSame([], $components);
    }

    public function testEmptyPlugins(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_plugins' => [],
        ]);
        $view = $form->createView();

        $plugins = json_decode($view->vars['attr']['data-plugins'], true);
        $this->assertSame([], $plugins);
    }

    public function testExtraOptionsDefaults(): void
    {
        $form = $this->factory->create(VvvebJsType::class);
        $view = $form->createView();

        $options = json_decode($view->vars['attr']['data-extra-options'], true);

        $this->assertSame('600px', $options['height']);
        $this->assertFalse($options['designerMode']);
        $this->assertFalse($options['readOnly']);
        $this->assertSame('', $options['uploadUrl']);
        $this->assertTrue($options['border']);
    }

    public function testCustomExtraOptions(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_options' => [
                'height' => '800px',
                'designerMode' => true,
                'uploadUrl' => '/vvvebjs/upload',
            ],
        ]);
        $view = $form->createView();

        $options = json_decode($view->vars['attr']['data-extra-options'], true);

        $this->assertSame('800px', $options['height']);
        $this->assertTrue($options['designerMode']);
        $this->assertSame('/vvvebjs/upload', $options['uploadUrl']);
    }

    public function testHeightWithInteger(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_options' => ['height' => 900],
        ]);
        $view = $form->createView();

        $options = json_decode($view->vars['attr']['data-extra-options'], true);
        $this->assertSame(900, $options['height']);
    }

    public function testBorderCustomValue(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_options' => ['border' => '2px solid #333'],
        ]);
        $view = $form->createView();

        $options = json_decode($view->vars['attr']['data-extra-options'], true);
        $this->assertSame('2px solid #333', $options['border']);
    }

    public function testCdnUrl(): void
    {
        $form = $this->factory->create(VvvebJsType::class);
        $view = $form->createView();

        $this->assertSame('https://cdn.example.com/vvvebjs', $view->vars['attr']['data-cdn-url']);
    }

    public function testBlockPrefix(): void
    {
        $form = $this->factory->create(VvvebJsType::class);
        $this->assertContains('vvvebjs', $form->createView()->vars['block_prefixes']);
    }

    public function testMixedEnumAndStringComponents(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_components' => [
                VvvebJsComponentGroup::HTML,
                'my-custom-components',
                VvvebJsComponentGroup::BOOTSTRAP5,
            ],
        ]);
        $view = $form->createView();

        $components = json_decode($view->vars['attr']['data-components'], true);
        $this->assertCount(3, $components);
        $this->assertSame(['html', 'my-custom-components', 'bootstrap5'], $components);
    }

    public function testReadOnlyOption(): void
    {
        $form = $this->factory->create(VvvebJsType::class, null, [
            'vvvebjs_options' => ['readOnly' => true],
        ]);
        $view = $form->createView();

        $options = json_decode($view->vars['attr']['data-extra-options'], true);
        $this->assertTrue($options['readOnly']);
    }
}
