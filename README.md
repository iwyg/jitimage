# Just in time image manipulation

version 0.2.* fo the jitimage package is still under development. Feel free to
give it a shot. Don't hasitate to file an [issue](https://github.com/iwyg/jitimage/issues). 

## Installation

Install this package via [composer](https://getcomposer.org/).

```bash
$ composer require thapp/jitimage@v0_2
```

or put this in your `composer.json` file

```json
{
	"require": {
		"thapp/jitimage": "dev-v0_2"
	}
}
```
and run

```bash
$ composer install
```
or, if you already have an existing installation

```bash
$ composer update
```

## Framework integration

### Laravel

Add the following line to the providers array located at `app/config/app.php`:

```php
<?php
return [
	// ...
	'providers' => [
		// ...
		'Thapp\JitImage\Laravel\JitImageServiceProvider'
	],
];
```

Also add the following line to the aliases array located at `app/config/app.php`:

```php
<?php
return [
	// ...
	'aliases' => [
		// ...
		'Thapp\JitImage\Laravel\Facades\JitImage'
	],
];

```

Next, copy the `config.php` from `vendor/thapp/jitimage/Laravel/config/config.php` to `app/config/packages/thapp/jitimage/config.php`.

### Silex

Not ready yet.


## Configuration


#### `paths <type array>`

Paths define the the source pathses from where your images are stored.
Depending on your loader configuraion this my be absolute or relative paths.

The path key also acts as the base path for dynamic processing routes and
cached routes.

#### `default_path <type string>`

This applies to the `JitImage class` and is used as a default lookup path when
ommitting the 'JitImage::from()' call.

#### `disable_dynamic_processing <type boolean>`

Set this to `true` if you want to completely disable dynamic processing routes.

#### `mode_constraints <type array>`

You can set scaling constraints for dynamic processing routes. Set the maximum
allowed value per processing mode, e.g. `2 => [2000, 2000]` will limit the
width/height to be 2000px for the corp an resize mode.

#### `recipes <type array>, [optional]`

Defines a set of predefined processing instructions per source path. You may
define as many recipes per source path as you like. The recipe route will then be
available under `/<alias>/<resource>`

#### `cache <type array>`

- `enabled <boolean>`:  
set this to false if you wan't to completely disable caching.

- `suffix <string>`:  
path suffix for cached routes, e.g. '/image/cached/…'

- `path <string>`:  
A local path where to store cached images. It is used if not otherwhise specified in the `cache.paths` array.

- `paths <array>`:  
Define a list of individual caching strategies per source path. You may disable
caching for one or more source paths by setting the `enabled` key to `false`.   
You may also set a different store location by setting the path on the `path` key.  
It is alos possible to completely override the default caching service by specifying a custom cache service.   
Note that the caching service must implement `Thapp\Image\Cache\CacheInterface`. 


#### `driver <type string>`

Define the driver for image processing. There're 3 different drivers available:
`gd`, `im`, and `imagick`

#### `loaders <type array>`

A list of source loaders you wan't to utilize.  
You may also use a custom loader. The loader must implement
`Thapp\Image\Loader\LoaderInterface`.

#### `trusted_sites <type array> [optional]`

This is only relevant, if you choose to utilize the
`Thapp\Image\Loader\RemoteLoader` source loader. You can define a list of
trusted sites from which you may fetch images via http. The list can contain valid regexps.


