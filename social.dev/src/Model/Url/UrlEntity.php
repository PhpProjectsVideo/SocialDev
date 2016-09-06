<?php

namespace PhpProjects\SocialDev\Model\Url;
use PhpProjects\SocialDev\Model\DomainEventManager;
use PhpProjects\SocialDev\Model\User\UserEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a url in the system
 * 
 * @package PhpProjects\SocialDev\Model\Url
 */
class UrlEntity
{
    const STATUS_LOADING_DATA = 1;

    const STATUS_DATA_LOADED = 2;
    
    /**
     * @var string
     */
    private $urlId;
    
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $keywords;

    /**
     * @var string
     */
    private $imageUrl;

    /**
     * @var int
     */
    private $status = self::STATUS_LOADING_DATA;

    /**
     * @var UserEntity
     */
    private $user;

    /**
     * @var int
     */
    private $timestamp;
    
    public function __construct(string $url, int $timestamp)
    {
        $this->url = $url;
        $this->title = $url;
        $this->urlId = self::generateUrlHash($url);
        $this->timestamp = $timestamp;
    }

    public static function generateUrlHash(string $url) : string
    {
        return hash('sha256', $url);
    }
    
    /**
     * @return string
     */
    public function getUrlId()
    {
        return $this->urlId;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
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
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return bool
     */
    public function isDataLoaded()
    {
        return $this->status == self::STATUS_DATA_LOADED;
    }

    public function setDataLoaded()
    {
        $this->status = self::STATUS_DATA_LOADED;
    }

    /**
     * @return UserEntity
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserEntity $user
     */
    public function setUser(UserEntity $user)
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getTimestamp() : int
    {
        return $this->timestamp;
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    /**
     * fires a domain event indicating that a new url has been persisted
     */
    public function fireNewUrlEvent()
    {
        DomainEventManager::getInstance()->dispatchEvent(DomainEventManager::EVENT_NEWURL, [ 'url' => $this ]);
    }
}