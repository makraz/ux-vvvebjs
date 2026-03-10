<?php

require dirname(__DIR__).'/vendor/autoload.php';

// Stub League\Flysystem interfaces when the package is not installed,
// so we can test FlysystemUploadHandler and the Flysystem DI path.
if (!interface_exists(League\Flysystem\FilesystemOperator::class)) {
    require __DIR__.'/Fixtures/FlysystemStub.php';
}
