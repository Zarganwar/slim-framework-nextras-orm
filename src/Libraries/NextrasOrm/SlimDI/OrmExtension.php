<?php


namespace App\Libraries\NextrasOrm\SlimDI;


use App\Libraries\Extensions\Extension;
use DI\Container;
use InvalidArgumentException;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Caching\Storages\FileStorage;
use Nette\Utils\Reflection;
use Nextras\Dbal\Connection;
use Nextras\Dbal\IConnection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Entity\Reflection\IMetadataParserFactory;
use Nextras\Orm\Entity\Reflection\MetadataParserFactory;
use Nextras\Orm\Exception\InvalidStateException;
use Nextras\Orm\Exception\RuntimeException;
use Nextras\Orm\Mapper\Dbal\DbalMapperCoordinator;
use Nextras\Orm\Model\IModel;
use Nextras\Orm\Model\IRepositoryLoader;
use Nextras\Orm\Model\MetadataStorage;
use Nextras\Orm\Model\Model;
use Nextras\Orm\Repository\IRepository;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use function assert;
use function class_exists;
use function preg_match_all;
use function sprintf;
use function str_replace;
use const PREG_SET_ORDER;

final class OrmExtension implements Extension
{


	public function __construct(
		private readonly Container $container,
		private readonly Config $config,
	) {}


	/**
	 * @return void
	 * @throws ReflectionException
	 */
	public function register(): void
	{
		$this->validateConfig();
		$this->setupCache();
		$this->setupConnection();
		$this->setupDbalMapperDependencies();
		$this->setupMetadataParserFactory();
		$this->setupRepositoryLoader();

		$repositories = $this->findRepositories();

		if ($repositories !== []) {
			$repositoriesConfig = Model::getConfiguration($repositories);
			$this->setupMetadataStorage($repositoriesConfig[2]);
			$this->setupModel($repositoriesConfig);
			$this->registerRepositories($repositories);
		}
	}


	private function setupConnection(): void
	{
		$this->container->set(
			IConnection::class,
			fn() => new Connection($this->config->connection)
		);
	}


	private function setupCache(): void
	{
		$this->container->set(Storage::class, fn() => new FileStorage(
			$this->config->cacheDirectory)
		);

		$this->container->set(Cache::class, fn() => new Cache(
			$this->container->get(Storage::class),
			'Nextras.Orm'
		));
	}


	private function setupDbalMapperDependencies(): void
	{
		$this->container->set(
			DbalMapperCoordinator::class,
			fn(ContainerInterface $c) => new DbalMapperCoordinator(
				$c->get(IConnection::class),
			)
		);
	}


	private function setupMetadataParserFactory(): void
	{
		$this->container->set(
			IMetadataParserFactory::class,
			fn(ContainerInterface $c) => new MetadataParserFactory()
		);
	}


	private function setupRepositoryLoader(): void
	{
		$this->container->set(
			IRepositoryLoader::class,
			fn(ContainerInterface $c) => new $this->config->repositoryLoaderClass($c)
		);
	}


	/**
	 * @param array<class-string<IEntity>, class-string<IRepository>> $entityClassMap
	 */
	private function setupMetadataStorage(array $entityClassMap): void
	{
		$this->container->set(
			MetadataStorage::class,
			fn(ContainerInterface $c) => new MetadataStorage(
				$entityClassMap,
				$c->get(Cache::class),
				$c->get(IMetadataParserFactory::class),
				$c->get(IRepositoryLoader::class),
			)
		);
	}


	private function validateConfig(): void
	{
		if (!class_exists($this->config->modelClass)) {
			throw new InvalidArgumentException("Model class {$this->config->modelClass} does not exist.");
		}

		if (!class_exists($this->config->repositoryLoaderClass)) {
			throw new InvalidArgumentException("Repository loader class {$this->config->repositoryLoaderClass} does not exist.");
		}
	}


	/**
	 * @return array<string, string>
	 * @phpstan-return array<string, class-string<IRepository>>
	 * @throws ReflectionException
	 */
	protected function findRepositories(): array
	{
		if ($this->config->modelClass === Model::class) {
			throw new InvalidStateException('Your model has to inherit from ' . Model::class . '. Use compiler extension configuration - model key.');
		}

		$modelReflection = new ReflectionClass($this->config->modelClass);
		assert($modelReflection->getFileName() !== false);

		$repositories = [];
		preg_match_all(
			'~^  [ \t*]*  @property(?:|-read)  [ \t]+  ([^\s$]+)  [ \t]+  \$  (\w+)  ()~mx',
			(string) $modelReflection->getDocComment(), $matches, PREG_SET_ORDER
		);

		/**
		 * @var string $type
		 * @var string $name
		 */
		foreach ($matches as [, $type, $name]) {
			/** @phpstan-var class-string<IRepository> $type */
			$type = Reflection::expandClassName($type, $modelReflection);
			if (!class_exists($type)) {
				throw new RuntimeException("Repository '{$type}' does not exist.");
			}

			$rc = new ReflectionClass($type);
			assert($rc->implementsInterface(IRepository::class), sprintf(
				'Property "%s" of class "%s" with type "%s" does not implement interface %s.',
				$this->config->modelClass, $name, $type, IRepository::class
			));

			$repositories[$name] = $type;
		}

		return $repositories;
	}


	private function registerRepositories(array $repositories): void
	{
		foreach ($repositories as $repositoryName => $repositoryClass) {
			$this->setupMapperService($repositoryName, $repositoryClass);
			$this->setupRepositoryService($repositoryName, $repositoryClass);
		}
	}


	protected function setupMapperService(string $repositoryName, string $repositoryClass): void
	{
		$mapperClass = str_replace('Repository', 'Mapper', $repositoryClass);

		if (!class_exists($mapperClass)) {
			throw new InvalidStateException("Unknown mapper for '{$repositoryName}' repository.");
		}

		$this->container->set(
			$mapperClass,
			fn(ContainerInterface $c) => new $mapperClass(
				$c->get(IConnection::class),
				$c->get(DbalMapperCoordinator::class),
				$c->get(Cache::class)
			)
		);
	}


	protected function setupRepositoryService(string $repositoryName, string $repositoryClass): void
	{
		$mapperClass = str_replace('Repository', 'Mapper', $repositoryClass);

		$this->container->set(
			$repositoryClass,
			function (ContainerInterface $c) use ($repositoryClass, $mapperClass): IRepository {
				/** @var IRepository $repository */
				$repository = new $repositoryClass(
					$c->get($mapperClass),
				);

				$repository->setModel($c->get(IModel::class));

				return $repository;
			}
		);
	}


	private function setupModel(array $repositoriesConfig): void
	{
		/** @var class-string<IModel> $modelClass */
		$modelClass = $this->config->modelClass;

		$this->container->set(
			IModel::class,
			fn(ContainerInterface $c) => new $modelClass(
				$repositoriesConfig,
				$c->get(IRepositoryLoader::class),
				$c->get(MetadataStorage::class),
			)
		);
	}

}