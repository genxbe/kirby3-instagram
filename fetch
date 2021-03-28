#!/usr/bin/env php
<?php
/**
 * Based on Janitor loading
 * https://github.com/bnomei/kirby3-janitor/
 */
$files = [
    __DIR__ . '/vendor/autoload.php',
    realpath(__DIR__ . '/../../../') . '/vendor/autoload.php',
    __DIR__ . '/kirby/bootstrap.php',
    realpath(__DIR__ . '/../../../') . '/kirby/bootstrap.php',
];

foreach($files as $file) {
    if (file_exists($file)) {
        require $file;
    }
}

$kirby = new Kirby;

if(site()->instagramToken()->IsEmpty())
{
    throw new \Exception('Instagram token is required.');
}

(new X\Instagram)->fetch();
