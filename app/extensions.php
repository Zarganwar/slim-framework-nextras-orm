<?php

use App\Libraries\Extensions\ExtensionLoader;
use App\Libraries\NextrasOrm\SlimDI\OrmExtension;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;


return function (ContainerBuilder $containerBuilder) {
	$containerBuilder->addDefinitions([
		ExtensionLoader::class => fn(ContainerInterface $c) => new ExtensionLoader(
			// Add extensions
			$c->get(OrmExtension::class),
		),
	]);
};
