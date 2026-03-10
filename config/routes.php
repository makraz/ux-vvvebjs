<?php

use Makraz\VvvebJsBundle\Controller\VvvebJsUploadController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('vvvebjs_upload', '/vvvebjs/upload')
        ->controller([VvvebJsUploadController::class, 'upload'])
        ->methods(['POST'])
    ;
};
