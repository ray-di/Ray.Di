<?php
// load vendor/*
require_once __DIR__ . '/Aura.Di/src.php';
require_once __DIR__ . '/Ray.Aop/src.php';
require_once dirname(__DIR__) . '/vendor/Doctrine.Common/lib/Doctrine/Common/ClassLoader.php';
$commonLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', dirname(__DIR__) . '/vendor/Doctrine.Common/lib');
$commonLoader->register();