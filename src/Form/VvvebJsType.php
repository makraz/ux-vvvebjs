<?php

namespace Makraz\VvvebJsBundle\Form;

use Makraz\VvvebJsBundle\DTO\Enums\VvvebJsComponentGroup;
use Makraz\VvvebJsBundle\DTO\Enums\VvvebJsPlugin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VvvebJsType extends AbstractType
{
    public function __construct(
        private readonly string $cdnUrl = 'https://cdn.jsdelivr.net/gh/givanz/VvvebJs@master',
    ) {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $components = $this->resolveComponentGroups($options['vvvebjs_components']);
        $plugins = $this->resolvePlugins($options['vvvebjs_plugins']);

        $extraOptions = $options['vvvebjs_options'];

        if (\is_callable($extraOptions)) {
            $extraResolver = new OptionsResolver();
            self::configureExtraOptions($extraResolver);
            $extraOptions($extraResolver);
            $extraOptions = $extraResolver->resolve([]);
        }

        $view->vars['attr']['data-components'] = json_encode($components);
        $view->vars['attr']['data-plugins'] = json_encode($plugins);
        $view->vars['attr']['data-extra-options'] = json_encode($extraOptions);
        $view->vars['attr']['data-cdn-url'] = $this->cdnUrl;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'error_bubbling' => true,
            'vvvebjs_components' => [
                VvvebJsComponentGroup::COMMON,
                VvvebJsComponentGroup::HTML,
                VvvebJsComponentGroup::ELEMENTS,
                VvvebJsComponentGroup::BOOTSTRAP5,
            ],
            'vvvebjs_plugins' => [
                VvvebJsPlugin::CODE_MIRROR,
            ],
            'vvvebjs_options' => static function (OptionsResolver $extraResolver) {
                self::configureExtraOptions($extraResolver);
            },
        ]);

        $resolver->setAllowedTypes('vvvebjs_components', 'array');
        $resolver->setAllowedTypes('vvvebjs_plugins', 'array');
        $resolver->setAllowedTypes('vvvebjs_options', ['array', 'callable']);
    }

    private static function configureExtraOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('height', '600px')
            ->setAllowedTypes('height', ['int', 'string'])
        ;
        $resolver
            ->setDefault('designerMode', false)
            ->setAllowedTypes('designerMode', 'bool')
        ;
        $resolver
            ->setDefault('readOnly', false)
            ->setAllowedTypes('readOnly', 'bool')
        ;
        $resolver
            ->setDefault('uploadUrl', '')
            ->setAllowedTypes('uploadUrl', 'string')
        ;
        $resolver
            ->setDefault('border', true)
            ->setAllowedTypes('border', ['bool', 'string'])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'vvvebjs';
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    /**
     * @param array<VvvebJsComponentGroup|string> $groups
     *
     * @return list<string>
     */
    private function resolveComponentGroups(array $groups): array
    {
        $resolved = [];

        foreach ($groups as $group) {
            if ($group instanceof VvvebJsComponentGroup) {
                $resolved[] = $group->value;
            } elseif (\is_string($group)) {
                $resolved[] = $group;
            }
        }

        return $resolved;
    }

    /**
     * @param array<VvvebJsPlugin|string> $plugins
     *
     * @return list<string>
     */
    private function resolvePlugins(array $plugins): array
    {
        $resolved = [];

        foreach ($plugins as $plugin) {
            if ($plugin instanceof VvvebJsPlugin) {
                $resolved[] = $plugin->value;
            } elseif (\is_string($plugin)) {
                $resolved[] = $plugin;
            }
        }

        return $resolved;
    }
}
