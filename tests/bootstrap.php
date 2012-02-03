<?php
// bootstrap for test
// vendor/*
require_once dirname(__DIR__) . '/vendor/Doctrine.Common/lib/Doctrine/Common/ClassLoader.php';
$commonLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', dirname(__DIR__) . '/vendor/Doctrine.Common/lib');
$commonLoader->register();
require_once dirname(__DIR__) . '/vendor/Ray.Aop/src.php';


require_once dirname(__DIR__) . '/src.php';
require_once __DIR__ . '/src.php';
