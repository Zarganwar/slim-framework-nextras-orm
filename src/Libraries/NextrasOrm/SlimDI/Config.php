<?php


namespace App\Libraries\NextrasOrm\SlimDI;


final class Config
{
	/**
	 * @param array<string, mixed> $connection
	 */
	public function __construct(
		public readonly string $cacheDirectory,
		public readonly string $modelClass,
		public readonly array $connection = [],
		public readonly string $repositoryLoaderClass = RepositoryLoader::class,
		public readonly bool $initializeMetadata = false,
	) {}

}