<?php

namespace PhpProjects\SocialDev\Model\Feed;

use PhpProjects\SocialDev\Application\SocialApplication;
use PhpProjects\SocialDev\Model\Url\UrlEntity;
use PhpProjects\SocialDev\Model\User\UserEntity;

/**
 * Models data in a user feed
 */
class FeedItemEntity
{
    /**
     * @var int
     */
    private $feedItemId;

    /**
     * @var UserEntity
     */
    private $user;

    /**
     * @var UserEntity
     */
    private $author;

    /**
     * @var UrlEntity
     */
    private $url;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $detailUrl;

    /**
     * @var string
     */
    private $imageUrl;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * FeedItemEntity constructor.
     * @param UserEntity $user
     * @param UserEntity $author
     * @param UrlEntity $url
     * @param string $description
     * @param string $detailUrl
     * @param int $timestamp
     */
    public function __construct(UserEntity $user, UserEntity $author, UrlEntity $url, string $description, string $detailUrl, int $timestamp)
    {
        $this->user = $user;
        $this->author = $author;
        $this->url = $url;
        $this->description = $description;
        $this->detailUrl = $detailUrl;
        $this->timestamp = $timestamp;
    }

    /**
     * @return int
     */
    public function getFeedItemId()
    {
        return $this->feedItemId;
    }

    /**
     * @return UserEntity
     */
    public function getUser() : UserEntity
    {
        return $this->user;
    }

    /**
     * @return UserEntity
     */
    public function getAuthor() : UserEntity
    {
        return $this->author;
    }

    /**
     * @return UrlEntity
     */
    public function getUrl() : UrlEntity
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return str_replace(
            [
                '{{author}}',
                '{{url}}'
            ],
            [
                '<strong>' . htmlentities($this->getAuthor()->getUsername()) . '</strong>',
                '<strong>' . htmlentities($this->getUrl()->getTitle()) . '</strong>',
            ],
            htmlentities($this->description)
        );
    }

    /**
     * @return string
     */
    public function getDetailUrl()
    {
        return $this->detailUrl;
    }
    
    public function generateUrl(SocialApplication $app)
    {
        list($route, $params) = explode('?', $this->getDetailUrl(), 2);
        parse_str($params, $routeParams);
        return $app->url($route, $routeParams);
    }

    /**
     * @return int
     */
    public function getTimestamp() : int
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl(string $imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }
}