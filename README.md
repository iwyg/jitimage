jitimage
========   

[![Build Status](https://travis-ci.org/iwyg/jitimage.png?branch=development)](https://travis-ci.org/iwyg/jitimage)

**Just In Time** image manipulation with integration for [Laravel 4](http://laravel.com/), supports [GD](http://www.php.net/manual/en/book.image.php), [ImageMagick](http://imagemagick.org/), and [Imagick](http://www.php.net/manual/en/book.imagick.php).


## Installation

Add thapp/jitimage as a requirement to composer.json:

```json
{
    "require": {
        "php":">=5.4.0",
        "thapp/jitimage": "dev-development"
    },
    "repositories": [
    	{
    		"type":"vcs",
    		"url":"https://github.com/iwyg/jitimage.git"
    	}
    ]
}
```

Then run `composer update` or `composer install`

Next step is to tell laravel to load the serviceprovider. In `app/config/app.php` add

```php
  // ...
  'Thapp\XmlConf\JitImageServiceProvider' 
  // ...
```
to the `providers` array.

### Publish configuration

```
php artisan config:publish thapp/jitimage
```

## Configuration

##### `route (string)`  

The base route for dynamic image processing   
##### `cacheroute (string)`:    

The base route for retrieving images by their cache id
##### `base (string)`:    

The filesystem base path to where your images are stored.

##### `driver (string)`   

The processing driver. Available drivers are `im`, `gd` and `ìmagick`
##### `cache (array)`  

An array of environments were imagecache should be enabled
##### `quality (string)`

compression quality, 0 - 100 (higher is better but also larger)
##### `imagemagick (array)`  

This array takes two values: `path`, the path to the imagick binary, and `bin`, the binary name.  
Typically the binary name is `convert`.  

##### `filter (array)`  

An array of available filter that should be enabled by default

##### `recepies (array)`  

An array of predefined parameters that are aliased to a root, e.g.

```php

'recepies' => [
	'thumbs' => '2/200/200/5, filter:gs'
],

```

would create a route thumbs that could be called like `http://example.com/thumbs/path/to/my/image.jpg`.    
Defining recipies will disable dynamic image processing. 

##### `trusted_sites (array)`  

An array of trusted sites for processing remote files  






## Usage

### Dynamic image processing

A word of warning. Dynamic image processing can harm you system and should be disabled in production. 

Anatomy of an image url:

`{base}/{parameter}/{imagesource}/filter:{filter}`

Parameter consists of one to five components, `mode`, `width`, `height`, `gravity` (crop position), and `background`

An Image url my look like this: `http://exmaple.com/images/2/200/200/5/path/to/my/image.jpg` 
To apply additional filters, the filter url segment is appended. The filter segments starts with `filter:` followed by the filter alias and the filter options. Filters are separated by a double colon `:`, filter parameter are separated by a semicolon `;`, eg `filter:gs;s=100;c=1:circ;o=12`. 


### Modes

- mode 0 : passthrough, no processing
- mode 1 : resize 
- mode 2 : resize and crop  
- mode 3 : crop 
- mode 4 : best fit


### using the facade class

This is a convenient way to scale images within your blade templates. It will create an imageurl similar to `/jit/storage/jit_139e2ead8b71b8c7e52a36a378835961.jpg`

```php

// proportionally resize the image have a width of 200px:
JitImage::source('path/to/myimage.jpg')->resize(200, 0);

// resize the image have a width and height of 200px (ignores aspect ratio):
JitImage::source('path/to/myimage.jpg')->resize(200, 200);

// crop 500px * 500px of the image from the center, creates a frame if image is smaller.
JitImage::source('path/to/myimage.jpg')->crop(500, 500, 5);

// You may also specify a background color for the frame:
JitImage::source('path/to/myimage.jpg')->crop(500, 500, 5, fff);

// crop 500px * 500px of the image from the center, resize image if image is smaller:
JitImage::source('path/to/myimage.jpg')->cropAndResize(500, 500, 5);

// resize the image to best fit within the given sizes:
JitImage::source('path/to/myimage.jpg')->fit(200, 200);

// crop 200px * 200px of the image from the center, resize image if image is smaller and apply a greyscale filter:
JitImage::source('path/to/myimage.jpg')->filter('gs')->cropAndResize(200, 200, 5);


```

### Artisan commands

There's really just one command right now. `php artisan jitimage:clearcache` will clear the whole image cache. 



