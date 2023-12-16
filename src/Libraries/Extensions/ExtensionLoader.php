<?php


namespace App\Libraries\Extensions;


final class ExtensionLoader
{

	private readonly array $extensions;

	public function __construct(
		Extension ...$extensions,
	) {
		$this->extensions = $extensions;
	}


	public function load(): void
	{
		foreach ($this->extensions as $extension) {
			$extension->register();
		}
	}

}