Ray.Di
=======
Guice style annotation-driven dependency injection framework for PHP
--------------------------------------------------------------------

This project was created in order to get Guice style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. [Guice]((http://code.google.com/p/google-guice/wiki/Motivation?tm=6) is a Java dependency injection framework developed by Google. 

 * Supports some of the JSR-330 object lifecycle annotations (@PostConstruct, @PreDestroy)
 * Provides an AOP Alliance-compliant aspect-oriented programming implementation.
 * [Aura.Di](http://auraphp.github.com/Aura.Di ) extended.
 * [Doctrine.Commons](http://www.doctrine-project.org/projects/common) annotation.

_Not all features of Guice have been implemented._

[![Build Status](https://secure.travis-ci.org/koriym/Ray.Di.png?branch=master)](http://travis-ci.org/koriym/Ray.Di)

Requirement
---------

* PHP 5.4+
* (APC) 

Documentation
=============

Available at Google Code.

 [http://code.google.com/p/rayphp/wiki/Motivation?tm=6](http://code.google.com/p/rayphp/wiki/Motivation?tm=6)


Installation
============

### Install with Composer
If you're using [Composer](https://github.com/composer/composer) to manage dependencies, you can add Ray.Di with it.

	{
		"require": {
			"ray/di": "*"
		}
	}

Testing Ray.Di
==============

Here's how to install Ray.Di from source to run the unit tests and samples.

```
$ git clone git://github.com/koriym/Ray.Di.git
$ cd Ray.Di
$ composer.phar install
$ phpunit
$ php doc/sample/00-newsletter.php
$ php doc/sample/01-db/main.php
$ cd doc/zf2-di-tests-clone/
$ php runall.php
```

