<?php

namespace PhpProjects\SocialDev\Model\Url;

use Doctrine\ORM\EntityManager;
use Goutte\Client as GoutteClient;

/**
 * populates url entities via data retrieved from a crawler.
 * 
 * @package PhpProjects\SocialDev\Model\Url
 */
class HttpUrlService
{
    /**
     * @var GoutteClient
     */
    private $client;

    /**
     * HttpUrlProvider constructor.
     * @param GoutteClient $client
     */
    public function __construct(GoutteClient $client)
    {
        $this->client = $client;
    }

    /**
     * Populates a url entity using the html found at the given http url.
     * 
     * @param UrlEntity $url
     */
    public function getUrlEntityFromUrl(UrlEntity $urlEntity)
    {
        $crawler = $this->client->request('GET', $urlEntity->getUrl());

        $nodes = $this->getUrlMetaData($crawler);

        $imageUrl = $nodes['property:og:image:secure_url']
            ?? $nodes['property:og:image:url']
            ?? $nodes['property:og:image']
            ?? $nodes['meta:twitter:image']
            ?? null;
        $urlEntity->setImageUrl($imageUrl);

        $title = $nodes['property:og:title']
            ?? $nodes['meta:twitter:title']
            ?? $nodes['title']
            ?? $urlEntity->getUrl();
        $urlEntity->setTitle($title);

        $description = $nodes['property:og:description']
            ?? $nodes['meta:twitter:description']
            ?? $nodes['meta:description']
            ?? null;
        $urlEntity->setDescription($description);

        $keywords = $nodes['meta:keywords']
            ?? null;
        $urlEntity->setKeywords($keywords);
        
        $urlEntity->setDataLoaded();
    }

    /**
     * @param $crawler
     * @return array
     */
    private function getUrlMetaData($crawler)
    {
        $nodes = [];
        $crawler->filter('meta[property]')->each(function ($node) use (&$nodes) {
            if (empty($nodes['property:' . $node->attr("property")]))
            {
                $nodes['property:' . $node->attr("property")] = $node->attr("content");
            }
        });
        $crawler->filter('meta[name]')->each(function ($node) use (&$nodes) {
            if (empty($nodes['meta:' . $node->attr("name")]))
            {
                $nodes['meta:' . $node->attr("name")] = $node->attr("content");
            }
        });
        $crawler->filter('link[rel]')->each(function ($node) use (&$nodes) {
            if (empty($nodes['link:' . $node->attr("rel")]))
            {
                $nodes['link:' . $node->attr("rel")] = $node->attr("href");
            }
        });
        $crawler->filter('title')->each(function ($node) use (&$nodes) {
            if (empty($nodes['title']))
            {
                $nodes['title'] = $node->text();
            }
        });
        return $nodes;
    }
}