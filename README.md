#JitImage

[![Author](http://img.shields.io/badge/author-iwyg-blue.svg?style=flat-square)](https://github.com/iwyg)
[![Source Code](http://img.shields.io/badge/source-thapp/jitimage-blue.svg?style=flat-square)](https://github.com/iwyg/jitimage/tree/develop)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/iwyg/jitimage/blob/develop/LICENSE.md)

[![Build Status](https://img.shields.io/travis/iwyg/jitimage/develop.svg?style=flat-square)](https://travis-ci.org/iwyg/jitimage)
[![Total Downloads](https://img.shields.io/packagist/dt/thapp/jitimage.svg?style=flat-square)](https://packagist.org/packages/thapp/jitimage)


## Just In Time Image manipulation: Library for HTTP based image manipulation. 

By default JitImage uses the [`Thapp/Image`](https://packagist.org/packages/thapp/image) php package to process images, but will also
work with the fantastic [`Imagine`](https://packagist.org/packages/imagine/imagine) library. 



## Installation:

Require `thapp/jitimage` in your `composer.json` file:

```json
{
	"require": {
		"thapp/jitimage":"dev-develop"
	} 
}
```

Installing:

```bash
$ composer install
```

Updating:

```bash
$ composer update
```

Run tests:

```bash
$ vendor/bin/phpunit -c phpunit.xml.dist
```

## Core Concepts

### Source loaders and resolvers

JitImage supports loading images from a variety of sources. In the example below, lets assume we have a local filesystem that hosts our images.

```php
<?php

use Thapp\JitImage\Loader\FilesystemLoader;
use Thapp\JitImage\Resolver\LoaderReslover;
use Thapp\JitImage\Resolver\PathResolver;

$loaderResolver = new LoaderResolver;
$pathResolver = new PathResolver;

$pathResolver->add('local', __DIR__.'public/images');
$loaderResolver->add('local', new FilesystemLoader);

// tries to resolve a given prefix path;
if (!$loader === $loaderResolver->resolve('local')) // returns the FilesystemLoader {
    //then error
}

if (null === $path = $pathResolver->resolve('local')) {
    //then error
}

$src = $loader->load($path . '/image.jpg');


```

### Custom loaders

You may create your own loaders, e.g. for loading images from a remote source like an Amazon s3 storage or an ftp server. 

Your custom loader must implement the `Thapp\JitImage\Loader\LoaderInterface` or simply extend from `Thapp\JitImage\Loader\AbstractLoader`.

```php

<?php

namespace Acme\Loaders;

use Thapp\JitImage\Loader\AbstractLoader

class AWSLoader extends AbstractLoader
{
    public function load($file)
    {
        //…
    }
    
    public function supports($path)
    {
        //…
    }
}

```


### Resolving images with parameters

Using the `ImageResolver` class, it is easy to resolve images from parameter strings.

```php
<?php


use Thapp\JitImage\Image\Processor;
use Thapp\JitImage\Resolver\PathResolver;
use Thapp\JitImage\Resolver\LoaderReslover;
use Thapp\Image\Driver\Imagick\Source;


$res = new ImageResolver(new Processor(new Source), $pathResolver, $loaderResolver);
$params = Parameters::fromString('2/400/400/5');

if ($resource = $res->resolve('images/source.jpg', $params)) {
    header('Content-Type: image/jpeg');
    echo $resource->getContents();
}


```

## Framework integration

JitImage comes prebundled with support for Laravel 5.* and Silex. 

### Laravel 5.*

In `config/app.php`, add:

```php
<?php

$providers => [
    // …
    'Thapp\JitImage\Framework\Laravel\JitImageServiceProvider'
];

$aliases => [
    // …
    'JitImage'      => 'Thapp\JitImage\Framework\Laravel\Facade\Jmg'
]

```
Then run

```bash
$ php artisan vendor:publish
```

from the command line.

`config/jmg.php`

**processor**  
The processor, default is `image`. `imagine` is experimental and likely to be removed from future releases. 
   
**driver**:  
The image driver. Available drivers are `imagick`, `im` (imagemagick binary), and `gd`.
  
**convert_path**  
If `im` is set for the driver, specify the path to the convert binary here.
  
**identify_path**  
If `im` is set for the driver, specify the path to the identify binary here.
  
**paths**  
Source paths aliases, e.g. 

```php
'images' => public_path().'/images', // will be available at `/images/<params>/image.jpg`
'remote' => 'http://images.example.com' // may be empty if you use absolute urls
``` 

**loaders**  

```php
'loaders' => [
    'images' => 'file',
    'remote' => 'http',
]
``` 

**disable\_dynamic\_processing**  
Disables image processing via dynamic urls.

**mode\_constraints**  
Set mode constraints on scaling values. This will only affect dynamic processing via URL. 

**recipes**
Predefined image formats, e.g.

```php
'thumbs' => [
    'images', '1/0/400,filter:palette;p=rgb:clrz;c=#0ff' // will be available at `/thumbs/image.jpg`
], 
```

**default\_cache**  
The default caching type. Shipped types are `file`

**default\_cache\_path**  
Directory path for local caches.


### Silex
