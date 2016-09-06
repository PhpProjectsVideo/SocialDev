<?php

use Doctrine\ORM\EntityManager;
use PhpProjects\SocialDev\Application\SocialApplication;
use PhpProjects\SocialDev\Model\Url\HttpUrlService;
use PhpProjects\SocialDev\Model\Url\UrlEntity;
use PhpProjects\SocialDev\Model\Url\UrlEntityRepository;
use PhpProjects\SocialDev\Model\User\UserEntity;
use PhpProjects\SocialDev\UI\FormTypes\RegistrationFormType;
use PhpProjects\SocialDev\UI\FormTypes\UrlFormType;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    if (!empty($app['user']) && $app['user']->isFullyRegistered())
    {
        return $app->redirect($app->url('user'));
    }

    $data = $app['user'] ?: new UserEntity();

    $form = $app->form($data, [], RegistrationFormType::class)->getForm();

    /* @var $em EntityManager */
    $em = $app['orm.em'];

    /* @var $urlRepository UrlEntityRepository */
    $urlRepository = $em->getRepository(UrlEntity::class);

    return $app->render('index.html.twig', [
        'error' => $app['security.last_error']($request),
        'form' => $form->createView(),
        'urls' => $urlRepository->getMostRecentUrlsSince(),
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


$app->get('/user/', function (Request $request) use ($app) {
    $form = $app->form([], [], UrlFormType::class)->getForm();

    /* @var $em EntityManager */
    $em = $app['orm.em'];

    /* @var $likeUrlRepo \PhpProjects\SocialDev\Model\LikedUrl\LikedUrlEntityRepository */
    $likeUrlRepo = $em->getRepository(\PhpProjects\SocialDev\Model\LikedUrl\LikedUrlEntity::class);
    
    $likedUrls = $likeUrlRepo->getRecentLikedUrls($app['user']);

    return $app->render('user/index.html.twig', [
        'form' => $form->createView(),
        'likedUrls' => $likedUrls
    ]);
})->bind('user');


$app->match('/user/likeUrl', function (Request $request) use ($app) {
    /* @var $em EntityManager */
    $em = $app['orm.em'];

    $data = $request->get('url_form', []);
    $form = $app->form($data, [], UrlFormType::class)->getForm();

    $form->handleRequest($request);

    if ($form->isValid())
    {
        $data = $form->getData();

        /* @var $user UserEntity */
        $user = $app['user'];

        /* @var $repo UrlEntityRepository */
        $repo = $em->getRepository(UrlEntity::class);

        $urlEntity = $repo->getOneByUrl($data['url']);
        if (empty($urlEntity))
        {
            $urlEntity = new UrlEntity($data['url'], time());
            $urlEntity->setUser($user);
            $em->persist($urlEntity);
        }

        $likedUrlEntity = $user->likeUrl($urlEntity);

        /* @var $validator ValidatorInterface */
        $validator = $app['validator'];

        $errors = $validator->validate($likedUrlEntity);
        if (count($errors) == 0)
        {
            $em->persist($likedUrlEntity);
            $em->flush();
        }

        return $app->redirect($app['url_generator']->generate('user'));
    }
    return $app->render('user/likeUrl.html.twig', [
        'form' => $form->createView(),
    ]);
})->bind('likeUrl');

$app->get('/poll/newUrls', function (Request $request) use ($app) {
    /* @var $em EntityManager */
    $em = $app['orm.em'];
    $timestamp = $request->get('timestamp') ?? 0;

    /* @var $urlRepository UrlEntityRepository */
    $urlRepository = $em->getRepository(UrlEntity::class);

    $startTime = time();

    //before we enter our loop we should let our session expire
    /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
    $session = $app['session'];
    $session->save();
    while (time() < $startTime + 10)
    {
        $urls = [];
        /* @var $url UrlEntity */
        foreach ($urlRepository->getMostRecentUrlsSince($timestamp) as $url)
        {
            $urls[] = [
                'url' => $url->getUrl(),
                'imageUrl' => $url->getImageUrl(),
                'title' => $url->getTitle(),
                'username' => $url->getUser() ? $url->getUser()->getUsername() : 'Anonymous',
                'description' => $url->getDescription(),
                'date' => date('F j, Y @ g:i a', $url->getTimestamp()),
                'timestamp' => $url->getTimestamp(),
            ];
        }

        if (count($urls))
        {
            return $app->json([
                'urls' => $urls,
            ]);
        }

        sleep(1);
    }

    return $app->json([ 'urls' => [] ]);
});

$app->match('/logout', function () {})->bind('logout');
//endregion

$app->run();