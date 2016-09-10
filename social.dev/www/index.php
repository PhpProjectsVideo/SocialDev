<?php

use PhpProjects\SocialDev\Application\SocialApplication;
use PhpProjects\SocialDev\Model\User\UserEntity;
use PhpProjects\SocialDev\UI\FormTypes\RegistrationFormType;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/config.php';

$app = new SocialApplication(['debug' => true]);

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

    $app['logout_path'] = $app->url('logout', [
        '_csrf_token' => $app['oauth.csrf_token']('logout')
    ]);
    $app['login_paths'] = $app['oauth.login_paths'];
});

//region Routes
$app->get('/', function (Request $request) use ($app) {

    $data = $app['user'] ?: new UserEntity();

    $form = $app->form($data, [], RegistrationFormType::class)->getForm();

    return $app->render('index.html.twig', [
        'error' => $app['security.last_error']($request),
        'form' => $form->createView(),
    ]);
})->bind('home');

$app->get('/login', function (Request $request) use ($app) {
    return $app->render('auth/login.html.twig', [
        'error' => $app['security.last_error']($request),
    ]);
});

$app->match('/register', function (Request $request) use ($app) {

    $data = $app['user'] ?: new UserEntity();

    $form = $app->form($data, [], RegistrationFormType::class)->getForm();

    $form->handleRequest($request);

    if ($form->isValid())
    {
        $data = $form->getData();

        $app['orm.em']->persist($data);
        $app['orm.em']->flush();

        return $app->redirect($app->url('home'));
    }
    return $app->render('auth/register.html.twig', [
        'form' => $form->createView(),
    ]);
})->bind('register');

$app->match('/logout', function () {})->bind('logout');
//endregion

$app->run();