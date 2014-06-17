# Just in time image manipulation

[![Build Status](https://travis-ci.org/iwyg/jitimage.png?branch=v0_2)](https://travis-ci.org/iwyg/jitimage)

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

Register the `Thapp\JitImage\Siliex\JitImageServiceProvider` on your
application

```php
<?php

$app->register(new Thapp\JitImage\Siliex\JitImageServiceProvider);

```

If you want to use the jitimage twig filters, register the  `Thapp\JitImage\Siliex\JitImageTwigHelperProvider`.
Make sure you've alse registered the [silex twig service provider](http://silex.sensiolabs.org/doc/providers/twig.html).

```php
<?php

$app->register(new Thapp\JitImage\Siliex\JitImageTwigHelperProvider);

```

You can configure JitImage using the bundled exaple config file `vendor/thapp/jitimage/Silex/config/default.php` as a blueprint.

## Configuration

Silex users must prefix keys with `jitimage.`, e.g. 

```php 
'$app['jitimage.default_path'] = 'image'
```

**paths** `<type array>`

Paths define the the source pathses from where your images are stored.
Depending on your loader configuraion this my be absolute or relative paths.

The path key also acts as the base path for dynamic processing routes and
cached routes.

**default_path** `<type string>`

This applies to the `JitImage class` and is used as a default lookup path when
ommitting the 'JitImage::from()' call.

**disable_dynamic_processing** `<type boolean>`

Set this to `true` if you want to completely disable dynamic processing routes.

**mode_constraints** `<type array>`

You can set scaling constraints for dynamic processing routes. Set the maximum
allowed value per processing mode, e.g. `2 => [2000, 2000]` will limit the
width/height to be 2000px for the corp an resize mode.

**recipes** `<type array>, [optional]`

Defines a set of predefined processing instructions per source path. You may
define as many recipes per source path as you like. The recipe route will then be
available under `/<alias>/<resource>`

**cache** `<type array>`

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


**driver** `<type string>`

Define the driver for image processing. There're 3 different drivers available:
`gd`, `im`, and `imagick`

**loaders** `<type array>`

A list of source loaders you wan't to utilize.  
You may also use a custom loader. The loader must implement
`Thapp\Image\Loader\LoaderInterface`.

**trusted_sites** `<type array> [optional]`

This is only relevant, if you choose to utilize the
`Thapp\Image\Loader\RemoteLoader` source loader. You can define a list of
trusted sites from which you may fetch images via http. The list can contain valid regexps.

## Usage

### Routes

#### Dynamic routes

A common jit route uri looks something like this `/path/<mode>/[<params>]/<source>/[filter:<filter>]`. It takes the processing mode as its first argument, followed by mode specific parameters and the image source. Filters can be declared after the source parameter, using the keyword `filter:`.

Filters are separated by a colon `:`, filter parameters are separated by
a semicolon `;`, e.g., `filter:overlay;c=f0e:circle` will add an overlay with color `#ff00ee` and apply a circle mask. 

#### Modes

**mode 0**  
Pass through, no scaling. 

**mode 1** `< width/height >`  
Resizes the image with the given width and height values and ignores aspect
ratio unless one of the values is zero.  

**mode 2** `< width/height/gravity >`  
Resize the image to fit within the cropping boundaries defined in width and height. 

Gravity explained:        

```
-------------  
| 1 | 2 | 3 |  
-------------  
| 4 | 5 | 6 |  
-------------  
| 7 | 8 | 9 |  
-------------  
```

**mode 3** `< width/height/gravity/[color] >`  
Crops the image with cropping boundaries defined in width and height. Will
create a frame if the image is smaller than the cropping area. 

**mode 4** `< width/height >`  
Best fit  within the given bounds.

**mode 5** `< percentage >`  
Percrentual scale. 

**mode 6** `< pixelcount >`  
Pixel limit.

### Templates

#### Twig

Takes an image `source.jpg` from the image source path 
and applies the `thumb` recipe.

```html
<img src="{{ 'source.jpg' | jmg_from('image') | jmg_make('thumb', true) }}"/>
```

Takes an image `source.jpg` from the image source path,
performs a `200px * 200px` crop with a gravity of `5`, and applies an `overlay`
filter.

```html
<img src="{{ 'source.jpg' | jmg_from('image') | jmg_crop_resize(200, 200, 5, 'overlay;c=ccc', true) }}"/>
```

You can also use the `jmg` twig function to dircetly access the jitimage service.

```html
<img src="{{ jmg('image').load('source.jpg').filter('circle').get() }}"/>
```

#### Blade (laravel)

Using blade, you can directly utilize the `JitImage` facade. 

Takes an image `source.jpg` from the image source path,
performs a `200px * 200px` crop with a gravity of `5`, and applies an `overlay`

```php

JitImage::from('image')
	->load('source.jpg')
	->withExtension() /* print image extension */
	->filter('overlay;c=ccc') /* or ->addFilter('overly', ['c' => 'ccc'])*/
	->cropAndResize(200, 200, 5);

```

Takes an image `source.jpg` from the image source path 
and applies the `thumb` recipe.

```php

JitImage::from('image')
	->load('source.jpg')
	->make('thumb');

```

### Filters

JitImage comes with 4 predfined filters, `Greyscale`, `Circle`, `Overlay`,
`Colorize`, and `Convert` (since v0.1.3):

(**Note:** since v0.1.4. calling invalid arguments on a filter will throw an
[`\InvalidArgumentException`](http://php.net/manual/en/class.invalidargumentexception.php))

-----------------

##### Greyscale

**alias** `greyscale`  
**parameters** (not available for the `gd` driver) 

```
`b` (Brightness){integer}, 0-100
```
```
`s` (Satturation){integer}, 0-100
```
```
`h` (Hue){integer}, 0-100
```
```
`c` (Contrast){integer} 0 or 1 
```

##### Circle

**alias** `circle`  
**parameters** 

```
`o` {integer} offset, any positive integer value
```

##### Overlay

**alias** `overlay`  
**parameters** 

```
`a` (alpha) {float} a float value between 0 and 1
```
```
`c` (color) {string} hex representation of an rgb value
```

##### Colorize

**alias** `colorize`  
**parameters** 

```
`c` (color){string} hex representation of an rgb value
```

##### Convert

**alias** `convert`  
**parameters** 

```
`f` (file format){string} a valid image file extension such as `png`, `jpg`, etc.
```
