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
        "thapp/jitimage": "~0.1"
    }
}
```

Then run `composer update` or `composer install`

Next step is to tell laravel to load the service provider. In `app/config/app.php` add

```php
  // ...
  'Thapp\JitImage\JitImageServiceProvider' 
  // ...
```
to the `providers` array and add `JitImage` to the alias array:

```php
'aliases' => [
   'JitImage' => 'Thapp\JitImage\Facades\JitImage'
 ],
```

### Publish configuration

```
php artisan config:publish thapp/jitimage
```

## Configuration

##### `route (string)`  

The base route for dynamic image processing   
##### `base (string)`    

The filesystem base path to where your images are stored.

##### `driver (string)`   

The processing driver. Available drivers are `im`, `gd` and `ìmagick`
##### `cache.route (string)`    

The base route for retrieving images by their cache id
##### `cache.path (string)`  

Cache directory
##### `cache.environments (array)`  

An array of environments were imagecache should be enabled
##### `cache.prefix (string)`  

Name prefix for cached images
##### `quality (string)`

compression quality, 0 - 100 (higher is better but also larger)
##### `imagemagick (array)`  

This array takes two values: `path`, the path to the imagick binary, and `bin`, the binary name.  
Typically the binary name is `convert`.  
##### `filter (array)`  

An array of available filter that should be enabled by default
##### `recipes (array)`  

An array of predefined parameters that are aliased to a route, e.g.


```php

'recipes' => [
	'thumbs' => '2/200/200/5, filter:gs'
],
```

would create a route 'thumbs' that could be called like `http://example.com/thumbs/path/to/my/image.jpg`.    
Defining recipes will disable dynamic image processing. 
##### `response-type (string)`  

You can choose `generic` or `xsend`. 

**Note:** your server must be capable to handle [x-send headers](https://www.google.com/search?q=x-send+headers&oq=x-send+headers&aqs=chrome.0.57&sourceid=chrome&ie=UTF-8) when using the
`xsend` response type.

```php
'response-type' => 'generic'
```

##### `trusted-sites (array)`  

A list of trusted sites that deliver assets, e.g. 
```
http://25.media.tumblr.com
```  
or as a regexp 

```  
http://[0-9]+.media.tumblr.(com|de|net)
```  



## Image Processors

### GD  

[GD](http://www.php.net/manual/en/book.image.php) is the standard php image processing library. Choose `gd` if you have either
no access to imagemagick or to the imagick php extension. 

There're a few downsides when using gd though, e.g. color profiles are not preserved, there's no support for preserving image sequences when processing an animated gif file. 
It also has a larger memory footprint so can become impossible in some cases (memory limitations on shared hosting
platforms, etc.).

### ImageMagick

Imagemagick is an incredible fast and versatile image processing library. Choose `im`
 in your `config.php`, if you have access to the `convert` binary. 

 For further information on imagemagick please visit the [official website](http://www.imagemagick.org/)

### Imagick

Imagick is imagemagick OOP for php. Choose `imagick` if you have the
[imagick](http://www.php.net/manual/en/book.imagick.php)
extensions installed but no access to the imagemagick binary.  

<!-- give me some air -->

## Usage

### Dynamic image processing

**A word of warning:** Dynamic image processing can <span class="danger" style="color:red;"><b>harm</b></span> you system and should be disabled in production. 

Anatomy of an image uri:

`{base}/{parameter}/{imagesource}/filter:{filter}`

Parameter consists of one to five components, `mode`, `width`, `height`, `gravity` (crop position), and `background`



An Image url my look like this: `http://exmaple.com/images/2/200/200/5/path/to/my/image.jpg` 
To apply additional filters, the filter url segment is appended. The filter segments starts with `filter:` followed by the filter alias and the filter options. Filters are separated by a double colon `:`, filter parameter are separated by a semicolon `;`, eg `filter:gs;s=100;c=1:circ;o=12`. 

#### Examples


Example URLs (assuming you have set `route` to `'images'` and your images are
stored in `public/uploads/images`.   


**resizing**  
Proportionally resize an image to 200px width:  

`http://example.com/images/1/200/0/uploads/images/image.jpg`

Resize an image to 200 * 200 px, ignoring its aspect ratio :  

`http://example.com/images/1/200/200/uploads/images/image.jpg`

Proportionally resize an image to best fit 400 * 280 px:  

`http://example.com/images/4/400/280/uploads/images/image.jpg`

Scale an image down to 50%:  

`http://example.com/images/5/50/uploads/images/image.jpg`

Limit to 200.000px pixel:  

`http://example.com/images/6/200000/uploads/images/image.jpg`

**cropping**  
Proportionally crop and resize an image to 200px * 200px with a gravity of
5 (center):    

`http://example.com/images/2/200/200/5/uploads/images/image.jpg`


### Predefined image processing 
(will disable dynamic processing)

You can alias your image processing with predefined recipes. 

#### Examples

Map mode 2 crop rescale, with a 200x200 px crop and a grey scale
      filter to `http://example.com/thumbs/uploads/images/image.jpg`:  

```php
	'thumbs' => '2/200/200/5, filter:gs'
```
 
Map mode 1 resize, with a resize of 800px width and a 
greyscale filter to `http://example.com/gellery/uploads/images/image.jpg`:
     
```php
   'gallery' => '1/800/0, filter:gs',
```     
Map mode 4 best fit, with a resize of max 800px width and 600px height, to `http://example.com/preview/uploads/images/image.jpg`:

```php
     
   'preview' => '4/800/600'
```     

#### Modes

**mode 0**  
Pass through, no processing. 

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

#### Converting image formats (since v0.1.4)

You may utilize [the `Convert` filter](#filter) (`conv`) to convert an image to
a different file format.  

As uri:

```php
// convert png to jpg:
'/images/<params>/<source>/filter:conf;f=jpg'
```

The [`JitImage`](#the-facade-class) class also provides some shortcut methods for this: `toJpeg`,
`toPng`, and `toGif`

```php
// convert png to jpg:
JitImage::source($filePNG)->toJpeg()->get();
JitImage::source($fileJPP)->toPng()->scale(50);
```

#### Filters

JitImage comes with 4 predfined filters, `GreyScale`, `Cirlce`, `Overlay`,
`Colorize`, and `Convert` (since v0.1.3):

(**Note:** since v0.1.4. calling invalid arguments on a filter will throw an
[`\InvalidArgumentException`](http://php.net/manual/en/class.invalidargumentexception.php))

##### GreyScale
```
- alias `gs`  
- options (not available for the `gd` driver) 
	- `b` (Brightness){integer}, 0-100
	- `s` (Satturation){integer}, 0-100
	- `h` (Hue){integer}, 0-100
	- `c` (Contrast){integer} 0 or 1 
```

##### Circle

```
- alias `circ`  
- options 
	- `o` {integer} offset, any positive integer value
```

##### Overlay

```
- alias `ovly`  
- options 
	- `a` (alpha) {float} a float value between 0 and 1
	- `c` (color) {string} hex representation of an rgb value
```

##### Colorize

```
- alias `clrz`  
- options 
	- `c` (color){string} hex representation of an rgb value
```

##### Convert

```
- alias `conv`  
- options 
	- `f` (file format){string} a valid image file extension such as `png`, `jpg`, etc.
```

### The facade class

This is a convenient way to scale images within your blade templates. It will create an imageurl similar to `/jit/storage/2egf4gfg/jit_139e2ead8b71b8c7e.jpg`

**Note**: this won't work if both caching and dynamic processing are disabled.  
**Note**: Filters (including the convert shorthands) must be called **before** any other maipulation method, as `resize`, `scale`, etc. will immediately return the computed filesource as string.

```php
// get the original image:
JitImage::source('path/to/myimage.jpg')->get();

// proportionally resize the image have a width of 200px:
JitImage::source('path/to/myimage.jpg')->resize(200, 0);

// resize the image have a width and height of 200px (ignores aspect ratio):
JitImage::source('path/to/myimage.jpg')->resize(200, 200);

// crop 500px * 500px of the image from the center, creates a frame if image is smaller.
JitImage::source('path/to/myimage.jpg')->crop(500, 500, 5);

// You may also specify a background color for the frame:
JitImage::source('path/to/myimage.jpg')->crop(500, 500, 5, 'fff');

// crop 500px * 500px of the image from the center, resize image if image is smaller:
JitImage::source('path/to/myimage.jpg')->cropAndResize(500, 500, 5);

// resize the image to best fit within the given sizes:
JitImage::source('path/to/myimage.jpg')->fit(200, 200);

// crop 200px * 200px of the image from the center, resize image if image is smaller and apply a greyscale filter:
JitImage::source('path/to/myimage.jpg')->filter('gs')->cropAndResize(200, 200, 5);

// Percentual scale the image:
JitImage::source('path/to/myimage.jpg')->scale(50);

// Limit the image to max. 200000px:
JitImage::source('path/to/myimage.jpg')->pixel(200000);

// Convert png to jpg:
JitImage::source('path/to/myimage.png')->toJpeg()->get();

```


## Register external filter

You may add your own filter classes to be used with JitImage. 

(more to come).

```php

Event::listen('jitimage.registerfilter', function ($driverName) {

    return [
        "mf" => sprintf("Namespace\\Filter\MyFilter\\%s%s", ucfirst($driverName) , 'MfFilter')
    ];

});
```


## Caching

### Artisan commands

There's really just one command right now. `php artisan jitimage:clearcache` will clear the whole image cache. 


### Deleting a cached image if its source file got replaced

It is possible to just delete cached images that have been created from
a certain source. So lets assume you have to replace an image called `myimage.jpg` in `uploads/images`, 
you could tell the cache class to to remove this specific cache directory. 

```php
$app['jitimage.cache']->delete('uploads/images/myimage.jpg');
```

You may also hoock this up to an upload event

```php

// attention! pseudo code:

Event::listen('image.upload', function ($event) use ($app) {
	$app['jitimage.cache']->delete($event->image);
});

```

### API

API documentation will be updated shortly.
