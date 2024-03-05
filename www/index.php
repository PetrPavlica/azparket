<?php declare(strict_types=1);

use Contributte\Middlewares\Application\IApplication as ApiApplication;
use Nette\Application\Application as UIApplication;

require __DIR__ . '/../vendor/autoload.php';
$self_dir = str_replace('/www', '', dirname($_SERVER['PHP_SELF']));
if ($self_dir == '/') {
    $self_dir = '';
}
$isApi = substr(str_replace($self_dir, '', $_SERVER['REQUEST_URI']), 0, 4) === '/api';

$configurator = App\Bootstrap::boot();
$container = $configurator->createContainer();
if ($isApi) {
    // Apitte application
    $container->getByType(ApiApplication::class)->run();
} else {
    // Nette application
    $container->getByType(UIApplication::class)->run();
}

