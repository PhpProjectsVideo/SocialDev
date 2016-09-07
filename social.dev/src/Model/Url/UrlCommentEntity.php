<?php

namespace PhpProjects\SocialDev\Model\Url;

use PhpProjects\SocialDev\Model\DomainEventManager;
use PhpProjects\SocialDev\Model\User\UserEntity;

/**
 * Models comments for a url
 */
class UrlCommentEntity
{
    /**
     * @var int
     */
    private $urlCommentId;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var UserEntity
     */
    private $author;

    /**
     * @var UrlEntity;
     */
    private $url;

    /**
     * @param string $comment
     * @param int $timestamp
     * @param UserEntity $author
     * @param UrlEntity $url
     */
    public function __construct(UrlEntity $url, UserEntity $author, $comment, $timestamp)
    {
        $this->url = $url;
        $this->author = $author;
        $this->comment = $comment;
        $this->timestamp = $timestamp;
    }

    /**
     * @return int
     */
    public function getUrlCommentId()
    {
        return $this->urlCommentId;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return UserEntity
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return UrlEntity
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Notify the domain of a new comment
     */
    public function fireNewCommentEvent()
    {
        DomainEventManager::getInstance()->dispatchEvent(DomainEventManager::EVENT_NEWCOMMENT, [ 'urlComment' => $this ]);
    }
}