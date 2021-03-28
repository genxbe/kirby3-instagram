<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('genxbe/instagram', [
    'options' => [
        'client_id' => '',
        'client_secret' => '',
        'redirect_uri' => u('axi/instagram'),
        'assetFolder' => 'instagram',
        'mediaFolder' => 'media',
        'db' => 'instagram.json',
    ],
    'blueprints' => [
        'linkInstagram' => __DIR__.'/blueprints/linkInstagram.yml',
    ],
    'routes' => [
        [
            'pattern' => 'axi/instagram',
            'action' => function() {
                (new X\Instagram)->getToken();

                go('error');
            },
            'method' => 'GET',
        ],
    ],

]);

if (!function_exists("instagramFeed")) {
    function instagramFeed()
    {
        return (new X\Instagram)->feed();
    }
}
