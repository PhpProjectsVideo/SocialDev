<?php

namespace PhpProjects\SocialDev\Model\LikedUrl;

use PhpProjects\SocialDev\Model\DomainEventManager;
use PhpProjects\SocialDev\Model\Url\UrlEntity;
use PhpProjects\SocialDev\Model\User\UserEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Aggregate root for liked urls from users
 */
class LikedUrlEntity
{
    /**
     * @var UserEntity
     */
    private $user;

    /**
     * @var UrlEntity
     */
    private $url;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * LikedUrlEntity constructor.
     * @param UserEntity $user
     * @param UrlEntity $url
     * @param int $timestamp
     */
    public function __construct(UserEntity $user, UrlEntity $url, int $timestamp)
    {
        $this->user = $user;
        $this->url = $url;
        $this->timestamp = $timestamp;
    }

    /**
     * Unwraps the users from a list of LikedUrlEntities
     *
     * @param array $likedUrls
     * @return array
     */
    public static function unwrapLikingUsers(array $likedUrls)
    {
        $users = [];
        foreach ($likedUrls as $urlLiker)
        {
            if ($urlLiker instanceof self)
            {
                $users[$urlLiker->getUser()->getUserId()] = $urlLiker->getUser();
            }
            else
            {
                throw new \InvalidArgumentException("All objects must be LikedUrlEntities");
            }
        }

        return $users;
    }

    /**
     * @return UserEntity
     */
    public function getUser() : UserEntity
    {
        return $this->user;
    }

    /**
     * @return UrlEntity
     */
    public function getUrl() : UrlEntity
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Validations to run for this entity
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity(array(
            'fields'  => ['user', 'url'],
            'message' => 'You have already liked this url.',
        )));
    }

    /**
     * Notify the domain of a new liked url
     */
    public function fireNewLikedUrlEvent()
    {
        DomainEventManager::getInstance()->dispatchEvent(DomainEventManager::EVENT_LIKEDURL, [ 'likedUrl' => $this ]);
    }
}