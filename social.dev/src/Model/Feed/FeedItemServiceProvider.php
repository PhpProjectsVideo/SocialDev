<?php

namespace PhpProjects\SocialDev\Model\Feed;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Goutte\Client as GoutteClient;
use PhpProjects\SocialDev\Model\DomainEventManager;
use PhpProjects\SocialDev\Model\LikedUrl\LikedUrlEntity;
use PhpProjects\SocialDev\Model\LikedUrl\LikedUrlEntityRepository;
use PhpProjects\SocialDev\Model\Url\UrlCommentEntity;
use PhpProjects\SocialDev\Model\User\UserEntity;
use PhpProjects\SocialDev\Model\User\UserFollowerEntity;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * A silex provider for the feed functionality.
 */
class FeedItemServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $app A container instance
     */
    public function register(Container $app)
    {
        DomainEventManager::getInstance()->attachListener(DomainEventManager::EVENT_LIKEDURL, function ($arguments) use ($app) {
            /* @var $em EntityManager */
            $em = $app['orm.em'];

            /* @var $likedUrl LikedUrlEntity */
            $likedUrl = $arguments['likedUrl'];

            /* @var $userFollowerRepository EntityRepository */
            $userFollowerRepository = $em->getRepository(UserFollowerEntity::class);
            $followers = $userFollowerRepository->findBy(['followee' => $likedUrl->getUser()]);

            $followingUsers = UserFollowerEntity::unwrapFollowingUsers($followers);

            /* @var $likedUrlRepository LikedUrlEntityRepository */
            $likedUrlRepository = $em->getRepository(LikedUrlEntity::class);
            $likedUrls = $likedUrlRepository->findBy(['url' => $likedUrl->getUrl()]);

            $urlLikers = LikedUrlEntity::unwrapLikingUsers($likedUrls);


            /* @var $user UserEntity */
            foreach ($followingUsers as $user)
            {
                $em->persist($user->addFollowedUserLikedUrlToFeed($likedUrl));
            }

            /* @var $user UserEntity */
            foreach ($urlLikers as $userId => $user)
            {
                if (empty($followingUsers[$userId]) && $user != $likedUrl->getUser())
                {
                    $em->persist($user->addUserSharingSameUrl($likedUrl));
                }
            }


            $em->flush();
        });

        DomainEventManager::getInstance()->attachListener(DomainEventManager::EVENT_NEWCOMMENT, function ($arguments) use ($app) {
            /* @var $em EntityManager */
            $em = $app['orm.em'];

            /* @var $urlComment UrlCommentEntity */
            $urlComment = $arguments['urlComment'];

            /* @var $userFollowerRepository EntityRepository */
            $userFollowerRepository = $em->getRepository(UserFollowerEntity::class);
            $followers = $userFollowerRepository->findBy(['followee' => $urlComment->getAuthor()]);

            $followingUsers = UserFollowerEntity::unwrapFollowingUsers($followers);

            /* @var $likedUrlRepository LikedUrlEntityRepository */
            $likedUrlRepository = $em->getRepository(LikedUrlEntity::class);
            $likedUrls = $likedUrlRepository->findBy(['url' => $urlComment->getUrl()]);

            $urlLikers = LikedUrlEntity::unwrapLikingUsers($likedUrls);

            /* @var $user UserEntity */
            foreach ($followingUsers as $user)
            {
                $em->persist($user->addFollowedUserCommentToFeed($urlComment));
            }

            /* @var $user UserEntity */
            foreach ($urlLikers as $userId => $user)
            {
                if (empty($followingUsers[$userId]))
                {
                    $em->persist($user->addUserCommentedOnSharedUrlToFeed($urlComment));
                }
            }

            $em->flush();
        });
    }
}