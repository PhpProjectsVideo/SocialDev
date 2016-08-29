<?php

use Gigablah\Silex\OAuth\OAuthServiceProvider;
use Gigablah\Silex\OAuth\Security\User\Provider\OAuthInMemoryUserProvider;
use Silex\Application;
use Silex\Provider\CsrfServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/config.php';

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

$app->register(new CsrfServiceProvider());
$app['form.csrf_provider'] = $app['csrf.token_manager'];

$app->register(new OAuthServiceProvider(), [
    'oauth.services' => [
        'Google' => [
            'key' => GOOGLE_API_CLIENT_ID,
            'secret' => GOOGLE_API_CLIENT_SECRET,
            'scope' => [
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile'
            ],
            'user_endpoint' => 'https://www.googleapis.com/oauth2/v1/userinfo'
        ],
    ]
]);

$app->register(new SecurityServiceProvider(), [
    'security.firewalls' => [
        'default' => [
            'pattern' => '^/',
            'anonymous' => true,
            'oauth' => [
                'login_path' => '/auth/{service}',
                'callback_path' => '/auth/{service}/callback',
                'check_path' => '/auth/{service}/check',
                'failure_path' => '/',
                'with_csrf' => true
            ],
            'logout' => [
                'logout_path' => '/logout',
                'with_csrf' => true
            ],
            'users' => new OAuthInMemoryUserProvider()
        ]
    ],
    'security.access_rules' => [
        ['^/auth', 'ROLE_USER']
    ]
]);
//endregion

$app->before(function () use ($app) {
    if (isset($app['security.token_storage'])) {
        $token = $app['security.token_storage']->getToken();
    } else {
        $token = $app['security']->getToken();
    }

    $app['user'] = null;

    if ($token && !$app['security.trust_resolver']->isAnonymous($token)) {
        $app['user'] = $token->getUser();
    }

    $app['logout_path'] = $app['url_generator']->generate('logout', [
        '_csrf_token' => $app['oauth.csrf_token']('logout')
    ]);
    $app['login_paths'] = $app['oauth.login_paths'];
});

//region Routes
$app->get('/', function (Request $request) use ($app) {

    return $app['twig']->render('index.html.twig', [
        'error' => $app['security.last_error']($request),
    ]);
})->bind('home');

$app->match('/logout', function () {})->bind('logout');
//endregion

$app->run();