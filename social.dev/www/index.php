<?php

use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Application();
$app['debug'] = true;

//region Service Providers
$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../views',
]);
$app->register(new DoctrineServiceProvider(), [
    'db.options' => [
        'driver' => 'pdo_mysql',
        'dbname' => 'social',
        'user' => 'social',
        'password' => 'social123',
    ],
]);
$app->register(new SessionServiceProvider(), [
    'session.db_options' => [
        'db_table'        => 'session',
        'db_id_col'       => 'session_id',
        'db_data_col'     => 'session_value',
        'db_lifetime_col' => 'session_lifetime',
        'db_time_col'     => 'session_time',
        'lock_mode'       => PdoSessionHandler::LOCK_ADVISORY,
    ],
    'session.storage.handler' => function () use ($app) {
        return new PdoSessionHandler(
            $app['db']->getWrappedConnection(),
            $app['session.db_options']
        );
    },
]);
//endregion

//region Routes
$app->get('/', function () use ($app) {
    $pageViews = $app['session']->get('pageViews') ?: 0;
    $pageViews++;
    
    $app['session']->set('pageViews', $pageViews);
    return $app['twig']->render('index.html.twig', array('pageViews' => $pageViews));
});
//endregion

$app->run();