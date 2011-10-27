# Ray.Di
## Annotation Based Dependency Injection System for PHP

## Overview ##
This project was created in order to get Guice style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. Guice is a Java dependency injection framework developed by Google (see http://code.google.com/p/google-guice/wiki/Motivation?tm=6). 

This package also supports some of the JSR-330 object lifecycle annotations, like @PostConstruct, @PreDestroy. 

 * This is a preview release.
 * Not all features of Guice have been implemented.

## Aura.Di ##
This project use Aura.Di component. 

"simple, elegant, and contains some of the cleanest PHP you will see anywhere."

Aura.Di: [http://auraphp.github.com/Aura.Di](http://auraphp.github.com/Aura.Di)

##Requiment##
 * PHP 5.3 / 5.4

##Quick Start##
    $ git clone git@github.com:koriym/Ray.Di.git
    $ cd Ray.Di
    $ git submodule update --init
    // original
    $ php doc/sample-01-db/original.php
    // with Ray.DI (+transaction +timer +template interception.)
    $ php doc/sample-01-db/main.php

## Documentation ##
Available at Google Code.

 [http://code.google.com/p/rayphp/wiki/Motivation?tm=6](http://code.google.com/p/rayphp/wiki/Motivation?tm=6)