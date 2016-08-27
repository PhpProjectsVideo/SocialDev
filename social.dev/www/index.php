<?php

use Silex\Application;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Application();

//Register Providers
$app->register(new \Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig');
});

$app->run();