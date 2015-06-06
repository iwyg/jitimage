#JitImage

[![Author](http://img.shields.io/badge/author-iwyg-blue.svg?style=flat-square)](https://github.com/iwyg)
[![Source Code](http://img.shields.io/badge/source-thapp/jitimage-blue.svg?style=flat-square)](https://github.com/iwyg/jitimage/tree/develop)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/iwyg/jitimage/blob/develop/LICENSE.md)

[![Build Status](https://img.shields.io/travis/iwyg/jitimage/develop.svg?style=flat-square)](https://travis-ci.org/iwyg/jitimage)
[![Total Downloads](https://img.shields.io/packagist/dt/thapp/jitimage.svg?style=flat-square)](https://packagist.org/packages/thapp/jitimage)


Just In Time Image manipulation: Library for HTTP based image manipulation. 

By default JitImage uses the `thapp/image` library to manipulate images, but it
also works with the fantastic `Imagine` lib. 

JitImage currently supports `Imagick`, `Imagemagick`, and `GD` drivers.


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


### Resolving an image with parameters
```php
<?php

use Thapp\Image\Image\Source;
use Thapp\JitImage\Image\Processor;
use Thapp\JitImage\Resolver\PathResolver;
use Thapp\JitImage\Resolver\LoaderReslover;


$res = new ImageResolver(new Processor(new Source), $pathResolver, $loaderResolver);
$params = Parameters::fromString('2/400/400/5');

if ($resource = $res->resolve('images/source.jpg', $params)) {
    header('Content-Type: image/jpeg');
    echo $resource->getContents();
}


```

## Framework integration

### Laravel 5.*
### Silex
