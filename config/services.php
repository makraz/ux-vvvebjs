<?php

use Makraz\VvvebJsBundle\Form\VvvebJsType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('form.ux_vvvebjs', VvvebJsType::class)
            ->arg('$cdnUrl', '%vvvebjs.cdn_url%')
            ->tag('form.type')
    ;
};
