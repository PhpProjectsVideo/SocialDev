<?php

namespace PhpProjects\SocialDev\Model\LikedUrl;

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
}