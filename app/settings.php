<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use App\Database\Model;
use App\Libraries\NextrasOrm\SlimDI\Config;
use DI\ContainerBuilder;
use Monolog\Logger;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
		'appName' => 'slim-nextras-orm-local',
		'tempDir' => __DIR__ . '/../var/temp',
		'cacheDir' => __DIR__ . '/../var/cache',
		Config::class => fn(ContainerInterface $c) => new Config(
			cacheDirectory: "{$c->get('cacheDir')}/",
			modelClass: Model::class,
			connection: [
				'driver' => 'mysqli',
				'host' => 'db',
				'username' => 'root',
				'password' => 'root',
				'database' => 'slim-nextras-orm-dev',
			],
		),
        SettingsInterface::class => function (ContainerInterface $c) {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => $c->get('appName'),
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
            ]);
        },
	]);
};
