## Simple Nextras ORM extension (loader) for Slim Framework  

This extension is compatible (with some small modifications) for all Frameworks (or DI) compatible with [Psr\Container\ContainerInterface PSR-11](https://www.php-fig.org/psr/psr-11/)

### Links
- [Slim Framework](https://www.slimframework.com/)
- [Nextras ORM (Documentation](https://github.com/nextras/orm)
- [Nextras ORM (Github)](https://nextras.org/orm/docs/main/)

### How to

- Simple extensions handling src/Libraries/Extensions But is not required, you can manage extensions as you wish
- Extension classes src/Libraries/NextrasOrm/SlimDI
- Configuration of Extension Config app/settings.php
- Registration of extensions app/extensions.php
- Registration of Extensions loader public/index.php `$container->get(\App\Libraries\Extensions\ExtensionLoader::class)->load();`
