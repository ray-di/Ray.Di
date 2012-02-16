Ray.Di
=======
Annotation based dependency injection for PHP
---------------------------------------------

This project was created in order to get Guice style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. [Guice]((http://code.google.com/p/google-guice/wiki/Motivation?tm=6) is a Java dependency injection framework developed by Google. 
This package also supports some of the JSR-330 object lifecycle annotations, like @PostConstruct, @PreDestroy. 

 * Supports some of the JSR-330 object lifecycle annotations (@PostConstruct, @PreDestroy)
 * Provides an AOP Alliance-compliant aspect-oriented programming implementation.
 * Using [Aura.Di](http://auraphp.github.com/Aura.Di ) , [Doctrine.Commons](http://www.doctrine-project.org/projects/common) as components.

_Not all features of Guice have been implemented._

[![Build Status](https://secure.travis-ci.org/koriym/Ray.Di.png)](http://travis-ci.org/koriym/Ray.Di)

Requiment
---------

* PHP 5.4+

## Documentation ##
Available at Google Code.

 [http://code.google.com/p/rayphp/wiki/Motivation?tm=6](http://code.google.com/p/rayphp/wiki/Motivation?tm=6)
 
Testing Ray.Di
==============

Here's how to install Ray.Di from source to run the unit tests:

```
$ git clone git://github.com/koriym/Ray.Aop.git
$ git submodule update --init
$ phpunit
```