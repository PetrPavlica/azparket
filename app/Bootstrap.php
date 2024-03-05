<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;
use Nette\Utils\Finder;


class Bootstrap
{
	public static function boot(): Configurator
	{
		$configurator = new Configurator;
		$appDir = dirname(__DIR__);

        if (!is_dir(__DIR__ . '/../temp/sessions')) {
            mkdir(__DIR__ . '/../temp/sessions', 0755, true);
        }

        $baseUri = trim(dirname($_SERVER['SCRIPT_NAME']), '\\/');
        if (PHP_SAPI !== 'cli') {
            $baseUri = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . (empty($baseUri) ? '/' : "/$baseUri/");
        }

        $configurator->addParameters([
            'rootDir' => realpath($appDir),
            'appDir' => $appDir.'/app',
            'wwwDir' => realpath($appDir . '/www'),
            'baseUri' => $baseUri,
        ]);

		$ipArr = [
			'secret@23.75.345.200',
			'secret@79.141.242.67' // WebRex Jihlava
		];

		$configurator->setDebugMode($ipArr); // enable for your remote IP
		$configurator->enableTracy($appDir . '/log');

		$configurator->setTimeZone('Europe/Prague');
		$configurator->setTempDirectory($appDir . '/temp');

		$loader = $configurator->createRobotLoader();
		$loader->addDirectory(__DIR__);
		$loader->register();

		$configurator->addConfig($appDir . '/config/common.neon');

		if ($_SERVER['SERVER_NAME'] == 'localhost' || PHP_SAPI == 'cli') {
			$configurator->addConfig($appDir . '/config/local.neon');
		} else {
			$configurator->addConfig($appDir . '/config/local.neon');
		}

		
		return $configurator;
	}


	public static function bootForTests(): Configurator
	{
		$configurator = self::boot();
		\Tester\Environment::setup();
		return $configurator;
	}
}
