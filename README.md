## Simple Nextras ORM extension (loader) for Slim Framework  

### Links
- [Slim Framework](https://www.slimframework.com/)
- [Nextras ORM (Documentation](https://github.com/nextras/orm)
- [Nextras ORM (Github)](https://nextras.org/orm/docs/main/)

### How to

- Extension handling src/Libraries/Extensions Bt is not required, you can manage extensions as you wish
- Extension classes src/Libraries/NextrasOrm/SlimDI
- Configuration of Extension Config app/settings.php
- Registration of extensions app/extensions.php
- Registration of Extensions loader public/index.php `$container->get(\App\Libraries\Extensions\ExtensionLoader::class)->load();`
