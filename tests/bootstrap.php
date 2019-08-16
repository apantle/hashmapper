<?php

include_once __DIR__ . '/../vendor/autoload.php';

$loader = new Composer\Autoload\ClassLoader();
$loader->addPsr4('Apantle\\HashMapper\\Test\\', __DIR__, true);
$loader->register();
