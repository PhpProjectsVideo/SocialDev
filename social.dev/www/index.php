<?php

use Doctrine\ORM\EntityManager;
use PhpProjects\SocialDev\Application\SocialApplication;
use PhpProjects\SocialDev\Model\Feed\FeedItemEntity;
use PhpProjects\SocialDev\Model\Feed\FeedItemEntityRepository;
use PhpProjects\SocialDev\Model\LikedUrl\LikedUrlEntity;
use PhpProjects\SocialDev\Model\LikedUrl\LikedUrlEntityRepository;
use PhpProjects\SocialDev\Model\Url\UrlCommentEntity;
use PhpProjects\SocialDev\Model\Url\UrlCommentEntityRepository;
use PhpProjects\SocialDev\Model\Url\UrlEntity;
use PhpProjects\SocialDev\Model\Url\UrlEntityRepository;
use PhpProjects\SocialDev\Model\User\UserEntity;
use PhpProjects\SocialDev\Model\User\UserEntityRepository;
use PhpProjects\SocialDev\Model\User\UserFollowerEntity;
use PhpProjects\SocialDev\Search\UserIndexingService;
use PhpProjects\SocialDev\UI\FormTypes\RegistrationFormType;
use PhpProjects\SocialDev\UI\FormTypes\UrlFormType;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    /* @var $user UserEntity */
    $user = $app['user'];

    /* @var $em EntityManager */
    $em = $app['orm.em'];

    /* @var $likeUrlRepo LikedUrlEntityRepository */
    $likeUrlRepo = $em->getRepository(LikedUrlEntity::class);
    
    $likedUrls = $likeUrlRepo->getRecentLikedUrls($app['user']);

    /* @var $userRespository UserEntityRepository */
    $userRespository = $em->getRepository(UserEntity::class);

    $similarUsers = $userRespository->getUsersWithSimilarLikes($app['user']);

    /* @var $userFollowerRespository \Doctrine\ORM\EntityRepository */
    $userFollowerRespository = $em->getRepository(UserFollowerEntity::class);

    $userFollowerEntities = $userFollowerRespository->findBy([
        'follower' => $user,
    ]);
    $followedUsers = UserFollowerEntity::unwrapFollowedUsers($userFollowerEntities);

    $similarUsers = array_diff($similarUsers, $followedUsers);

    /* @var $feedRepository FeedItemEntityRepository */
    $feedRepository = $em->getRepository(FeedItemEntity::class);
    $feed = $feedRepository->getRecentFeedItemsForUser($user);

    /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
    $session = $app['session'];
    $flashBag = $session->getFlashBag();

    return $app->render('user/index.html.twig', [
        'form' => $form->createView(),
        'likedUrls' => $likedUrls,
        'similarUsers' => $similarUsers,
        'followedUsers' => $followedUsers,
        'feed' => $feed,
        'flashMessage' => $flashBag->get('message', [ '' ])[0],
        'flashMessageType' => $flashBag->get('message-type', [ 'default' ])[0],
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


$app->match('/user/follow/{username}', function ($username) use ($app) {
    /* @var $em EntityManager */
    $em = $app['orm.em'];

    /* @var $user UserEntity */
    $user = $app['user'];


    /* @var $userRespository UserEntityRepository */
    $userRespository = $em->getRepository(UserEntity::class);

    /* @var $followee UserEntity */
    $followee = $userRespository->findOneBy(['username' => $username]);

    /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
    $session = $app['session'];
    $flashBag = $session->getFlashBag();
    if (empty($followee))
    {
        $flashBag->add('message-type', 'danger');
        $flashBag->add('message', 'Could not find user ' . $username);
    }
    else
    {
        /* @var $userFollowerRespository \Doctrine\ORM\EntityRepository */
        $userFollowerRespository = $em->getRepository(UserFollowerEntity::class);
        $userFollowerEntity = $userFollowerRespository->findOneBy([
            'follower' => $user,
            'followee' => $followee
        ]);

        if (empty($userFollowerEntity))
        {
            $userFollowerEntity = $user->followUser($followee);
            $em->persist($userFollowerEntity);
            $em->flush();

            $flashBag->add('message-type', 'success');
            $flashBag->add('message', 'Now following ' . $username);
        }
        else
        {
            $flashBag->add('message-type', 'warning');
            $flashBag->add('message', 'You were already following ' . $username);
        }
    }

    return $app->redirect($app->url('user'));
})->bind('follow-user');


$app->match('/user/unfollow/{username}', function ($username) use ($app) {
    /* @var $em EntityManager */
    $em = $app['orm.em'];

    /* @var $user UserEntity */
    $user = $app['user'];


    /* @var $userRespository UserEntityRepository */
    $userRespository = $em->getRepository(UserEntity::class);

    /* @var $followee UserEntity */
    $followee = $userRespository->findOneBy(['username' => $username]);

    /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
    $session = $app['session'];
    $flashBag = $session->getFlashBag();
    if (empty($followee))
    {
        $flashBag->add('message-type', 'danger');
        $flashBag->add('message', 'Could not find user ' . $username);
    }
    else
    {
        /* @var $userFollowerRespository \Doctrine\ORM\EntityRepository */
        $userFollowerRespository = $em->getRepository(UserFollowerEntity::class);
        $userFollowerEntity = $userFollowerRespository->findOneBy([
            'follower' => $user,
            'followee' => $followee
        ]);

        if (empty($userFollowerEntity))
        {
            $flashBag->add('message-type', 'warning');
            $flashBag->add('message', 'You weren\'t following ' . $username);
        }
        else
        {
            $em->remove($userFollowerEntity);
            $em->flush();

            $flashBag->add('message-type', 'success');
            $flashBag->add('message', 'No longer following ' . $username);
        }
    }

    return $app->redirect($app->url('user'));
})->bind('unfollow-user');

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
                'urlId' => $url->getUrlId(),
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

$app->get('/search/user', function (Request $request) use ($app) {
    $username = $request->get('username', false);

    $users = [];
    $error = '';
    if ($username !== false)
    {
        if (strlen($username) > 0)
        {
            /* @var $em EntityManager */
            $em = $app['orm.em'];

            /* @var $userRespository UserEntityRepository */
            $userRespository = $em->getRepository(UserEntity::class);

            /* @var $userIndexingService UserIndexingService */
            $userIndexingService = $app['elasticSearch.userIndexingService'];

            $users = $userRespository->getUsersFromSearch($username, $userIndexingService);
        }
        else
        {
            $error = 'You must specify a user name';
        }
    }

    return $app->renderView('search.html.twig', [
        'users' => $users,
        'error' => $error
    ]);
})->bind('searchUser');

$app->get('/search/user/{username}', function (string $username, Request $request) use ($app) {
    /* @var $em EntityManager */
    $em = $app['orm.em'];

    /* @var $userRespository UserEntityRepository */
    $userRespository = $em->getRepository(UserEntity::class);
    
    /* @var $userEntity UserEntity */
    $userEntity = $userRespository->findOneBy(['username' => $username]);
    if (empty($userEntity))
    {
        throw new NotFoundHttpException();
    }
    
    /* @var $likedUrlRepository LikedUrlEntityRepository */
    $likedUrlRepository = $em->getRepository(LikedUrlEntity::class);
    $likedUrls = $likedUrlRepository->getRecentLikedUrls($userEntity);
    
    return $app->renderView('userResult.html.twig', [
        'user' => $userEntity,
        'urls' => $likedUrls,
    ]);
})->bind('public-user');

$app->get('/url/{urlId}/', function ($urlId) use ($app) {
    /* @var $em EntityManager */
    $em = $app['orm.em'];
    /* @var $urlRepository UrlEntityRepository */
    $urlRepository = $em->getRepository(UrlEntity::class);
    /* @var $urlEntity UrlEntity */
    $urlEntity = $urlRepository->find($urlId);
    
    if (empty($urlEntity))
    {
        throw new NotFoundHttpException();
    }

    /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
    $session = $app['session'];
    $flashBag = $session->getFlashBag();

    return $app->renderView('urlResult.html.twig', [
        'url' => $urlEntity,
        'flashMessage' => $flashBag->get('message', [ '' ])[0],
        'flashMessageType' => $flashBag->get('message-type', [ 'default' ])[0],
    ]);
})->bind('url-details');


$app->get('/url/{urlId}/comments', function ($urlId) use ($app) {
    /* @var $em EntityManager */
    $em = $app['orm.em'];
    /* @var $urlRepository UrlEntityRepository */
    $urlRepository = $em->getRepository(UrlEntity::class);
    /* @var $urlEntity UrlEntity */
    $urlEntity = $urlRepository->find($urlId);

    if (empty($urlEntity))
    {
        throw new NotFoundHttpException();
    }

    /* @var $urlCommentRepository UrlCommentEntityRepository */
    $urlCommentRepository = $em->getRepository(UrlCommentEntity::class);
    $urlComments = $urlCommentRepository->getCommentsForUrlAfter($urlEntity);

    return $app->renderView('urlComments.html.twig', [
        'url' => $urlEntity,
        'comments' => $urlComments,
    ]);
})->bind('url-commentList');

$app->post('/user/comment/{urlId}', function ($urlId, Request $request) use ($app) {
    /* @var $em EntityManager */
    $em = $app['orm.em'];

    /* @var $user UserEntity */
    $user = $app['user'];

    /* @var $urlRepository UrlEntityRepository */
    $urlRepository = $em->getRepository(UrlEntity::class);
    /* @var $urlEntity UrlEntity */
    $urlEntity = $urlRepository->find($urlId);
    
    if (empty($urlEntity))
    {
        throw new NotFoundHttpException();
    }

    $comment = $request->get('comment');

    /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
    $session = $app['session'];
    $flashBag = $session->getFlashBag();

    if (empty($comment)) {
        $flashBag->add('message-type', 'danger');
        $flashBag->add('message', 'Your comment was empty');
    }
    else
    {
        $comment = $urlEntity->addComment($user, $comment);
        $em->persist($comment);
        $em->flush();
        
        $flashBag->add('message-type', 'success');
        $flashBag->add('message', 'Comment Added');
    }
    
    return $app->redirect($app->url('url-details', [ 'urlId' => $urlId ]));
})->bind('url-comment');

$app->get('/poll/comments/{urlId}', function ($urlId, Request $request) use ($app) {
    /* @var $em EntityManager */
    $em = $app['orm.em'];
    $lastCommentId = $request->get('lastComment') ?? '';

    /* @var $urlRepository UrlEntityRepository */
    $urlRepository = $em->getRepository(UrlEntity::class);
    /* @var $urlEntity UrlEntity */
    $urlEntity = $urlRepository->find($urlId);
    
    if (empty($urlEntity))
    {
        throw new NotFoundHttpException();
    }

    /* @var $urlCommentRepository UrlCommentEntityRepository */
    $urlCommentRepository = $em->getRepository(UrlCommentEntity::class);
    
    $urlComment = $urlCommentRepository->find($lastCommentId);
 
    $startTime = time();

    //before we enter our loop we should let our session expire
    /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
    $session = $app['session'];
    $session->save();
    while (time() < $startTime + 10)
    {
        $comments = [];
        /* @var $urlComment UrlCommentEntity */
        foreach ($urlCommentRepository->getCommentsForUrlAfter($urlEntity, $urlComment) as $urlComment)
        {
            $comments[] = [
                'commentId' => $urlComment->getUrlCommentId(),
                'username' => $urlComment->getAuthor()->getUsername(),
                'timestamp' => date('F j, Y @ g:i a', $urlComment->getTimestamp()),
                'comment' => $urlComment->getComment(),
            ];
        }

        if (count($comments))
        {
            return $app->json([
                'comments' => $comments,
            ]);
        }

        sleep(1);
    }

    return $app->json([ 'comments' => [] ]);
})->bind('poll-comments');

$app->get('/user/poll/feed', function (Request $request) use ($app) {
    /* @var $user UserEntity */
    $user = $app['user'];

    /* @var $em EntityManager */
    $em = $app['orm.em'];
    $timestamp = $request->get('timestamp') ?? 0;

    /* @var $feedRepository FeedItemEntityRepository */
    $feedRepository = $em->getRepository(FeedItemEntity::class);
 
    $startTime = time();

    //before we enter our loop we should let our session expire
    /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
    $session = $app['session'];
    $session->save();
    while (time() < $startTime + 10)
    {
        $feed = [];
        /* @var $feedItem FeedItemEntity */
        foreach ($feedRepository->getRecentFeedItemsForUser($user, $timestamp) as $feedItem)
        {
            $feed[] = [
                'description' => $feedItem->getDescription(),
                'url' => $feedItem->generateUrl($app),
                'imageUrl' => $feedItem->getImageUrl(),
                'timestamp' => $feedItem->getTimestamp(),
                'date' => date('F j, Y @ g:i a', $feedItem->getTimestamp()),
            ];
        }

        if (count($feed))
        {
            return $app->json([
                'feed' => $feed,
            ]);
        }

        sleep(1);
    }

    return $app->json([ 'feed' => [] ]);
})->bind('poll-feed');


$app->match('/logout', function () {})->bind('logout');
//endregion

$app->run();