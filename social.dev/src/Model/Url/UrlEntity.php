<?php

namespace PhpProjects\SocialDev\Model\Url;
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
    
    public function __construct(string $url)
    {
        $this->url = $url;
        $this->urlId = self::generateUrlHash($url);
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

    public function __toString()
    {
        return $this->getUrl();
    }
}