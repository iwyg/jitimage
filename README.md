#JitImage

<!--
[![Author](http://img.shields.io/badge/author-iwyg-blue.svg?style=flat-square)](https://github.com/iwyg)
[![Source Code](http://img.shields.io/badge/source-thapp/jitimage-blue.svg?style=flat-square)](https://github.com/iwyg/jitimage/tree/develop)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/iwyg/jitimage/blob/develop/LICENSE.md)

[![Build Status](https://img.shields.io/travis/iwyg/jitimage/develop.svg?style=flat-square)](https://travis-ci.org/iwyg/jitimage)
[![Total Downloads](https://img.shields.io/packagist/dt/thapp/jitimage.svg?style=flat-square)](https://packagist.org/packages/thapp/jitimage)
-->

Just In Time Image manipulation: Library for HTTP based image manipulation. 

By default JitImage uses the `thapp/image` library to manipulate images, but it
also works with the fantastic `Imagine` lib. 

JitImage currently supports `Imagick`, `Gmagick`, and `GD` drivers.


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
